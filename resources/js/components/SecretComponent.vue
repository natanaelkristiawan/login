<template>
    <div class="container">
        <!-- login form -->
        
        <div class="row mt-4" v-if="!isLoggedIn">
            <div class="col-6 offset-3" v-if="loginField">
                <h1>Sanctum Login</h1>
                <form action="#" @submit.prevent="handleLogin">
                    <div class="form-row">
                        <input type="email" name="email" class="form-control" v-model="formData.email" placeholder="Email Address">
                    </div>
                    <div class="form-row">
                        <input type="password" placeholder="Password" v-model="formData.password" name="password" class="form-control">
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn btn-primary">Sign in</button>
                        <a style="color: white" v-bind:href="google" class="btn btn-primary ml-3">Google Login</a>
                    </div>
                </form>
                <a href="#"  v-on:click="showRegister">Register</a>
                <a href="#"  v-on:click="showForgot">Forgot Password</a>
            </div> 
            <div class="col-6 offset-3" v-if="!loginField">
                <h1>Sanctum Register</h1>
                <form action="#" @submit.prevent="handleRegister">
                    <div class="form-row">
                        <input type="text" name="name" class="form-control" v-model="formRegister.name" placeholder="Name">
                    </div> 
                    <div class="form-row">
                        <input type="email" name="email" class="form-control" v-model="formRegister.email" placeholder="Email Address">
                        <span v-text="error.email"></span>
                    </div>
                    <div class="form-row">
                        <input type="password" placeholder="Password" v-model="formRegister.password" name="password" class="form-control">
                        <span v-text="error.password"></span>
                    </div>
                    <div class="form-row">
                        <input type="password" placeholder="Password" v-model="formRegister.password_confirmation" name="password_confirmation" class="form-control">
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn btn-primary">Register</button>
                        <a style="color: white" v-bind:href="google" class="btn btn-primary ml-3">Google Login</a>
                    </div>
                </form>
                 <a href="#" v-on:click="showLogin">Login</a>
                 <a href="#"  v-on:click="showForgot">Forgot Password</a>
            </div>

            <div class="col-6 offset-3 mt-5" v-if="forgotPassword">
                <h1>Forgot</h1>
                <form action="#" @submit.prevent="handleReset">
                    <div class="form-row">
                        <input type="email" name="email" class="form-control" v-model="formForgot.email" placeholder="email">
                        <span v-text="error.email_reset"></span>
                    </div> 
                     <div class="form-row">
                        <button type="submit" class="btn btn-primary">SendMail</button>
                        <button type="button" v-on:click="hideForgot" class="ml-2 btn btn-danger">Cancel</button>
                    </div>
                </form>
            </div>

        </div>
        
        <div class="row mt-4" v-if="isLoggedIn">
            <div class="col-6 offset-3">
                <h3>My Secret</h3>
                <div class="form-row" v-for="(value, key) in secrets">
                    <label v-text="key"></label>
                    <input class="form-control" type="text" readonly="" v-bind:value="value" name="">
                </div>
                <button @click.prevent="logout" class="btn btn-primary">Logout</button>
            </div>
        </div>
 
    </div>
</template>

<script>
    export default {
        props : ['googlelink', 'users', 'base'],
        data() {
            return {
                isLoggedIn: false,
                forgotPassword: false,
                secrets: [],
                formData : {
                    email : '',
                    password : ''
                },
                formForgot : {
                    email : ''
                },
                formRegister : {
                    email : '',
                    name : '',
                    password : '',
                },
                error : {
                    password : '',
                    email : '',
                    email_reset: ''
                },
                google : '',
                login : true,
                loginField : true
            }
        },
        created() {
            if (localStorage.getItem("auth")) {
                this.getSecrets();
            }
            this.google = this.googlelink;
            if(this.users) {
                localStorage.setItem('auth', true)
                this.getSecrets();
            }
        },
        mounted() {
            this.isLoggedIn = localStorage.getItem("auth");

        },
        methods: {
            handleLogin() {
                // send axios request the login route
                axios.get(this.base+'/sanctum/csrf-cookie').then(response => {
                    axios.post(this.base+'/login', this.formData).then(response => {
                        localStorage.setItem('auth', true)
                        this.isLoggedIn = true;
                        this.getSecrets();
                    })
                })
            },

            handleRegister() {
                 // send axios request the login route
                axios.get(this.base+'/sanctum/csrf-cookie').then(response => {
                    axios.post(this.base+'/register', this.formRegister).then(response => {
                        localStorage.setItem('auth', true)
                        this.isLoggedIn = true;
                        
                        this.getSecrets();
                    }).catch(error => {
                        this.error.password = error.response.data.errors.password[0]
                        this.error.email = error.response.data.errors.email[0]
                    })
                })
            },

            handleReset() {
                 axios.get(this.base+'/sanctum/csrf-cookie').then(response => {
                    axios.post(this.base+'/forgot-password', this.formForgot).then(response => {
                        this.hideForgot();
                        alert('checkemail')
                    }).catch(error => {
                        this.error.email_reset = error.response.data.message
                    })
                })
            },
            getSecrets() {
                axios.get(this.base+'/sanctum/csrf-cookie').then(response => {
                    axios.get(this.base+'/api/secrets').then(response => {
                        this.secrets = response.data;
                    })
                })
                
            },

            logout() {
                axios.get(this.base+'/sanctum/csrf-cookie').then(response => {
                    axios.post(this.base+'/logout', this.formData).then(response => {
                        this.isLoggedIn = false;
                        localStorage.removeItem("auth");
                    })
                });
            },

            showLogin() {
                this.loginField = true
                this.hideForgot();
            },

            showRegister() {
                this.loginField = false
                this.hideForgot();
            },

            showForgot() {
                this.forgotPassword = true
            },
            hideForgot() {
                this.forgotPassword = false
            }

        }
    }
</script>


<style type="text/css">
    .form-row {
        margin-bottom: 8px;
    }
</style>