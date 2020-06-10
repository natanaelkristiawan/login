<template>
    <div class="container">
        <!-- login form -->
        <div class="row mt-4">
            <div class="col-6 offset-3">
                <h1>Reset Password</h1>
                <form action="#" @submit.prevent="handleReset">
                    <div class="form-row">
                        <input type="password" name="password" class="form-control" v-model="formData.password" placeholder="Password">
                        <span v-text="error.password"></span>
                    </div>
                    <div class="form-row">
                        <input type="password" placeholder="Password" v-model="formData.password_confirmation" name="password_confirmation" class="form-control">
                    </div>
                    <div class="form-row">
                        <button type="submit" class="btn btn-primary">Reset</button>
                    </div>
                </form>

            </div> 
        </div>
 
    </div>
</template>

<script>
    export default {
        props : ['base', 'tokenuser'],
        data() {
            return {
                formData : {
                    password : '',
                    password_confirmation : '',
                    token : ''
                },
                error : {
                    password : ''
                }
            }
        },
        mounted() {
            this.formData.token = this.tokenuser;
        },
      
        methods: {
            handleReset() {
                // send axios request the login route
                axios.get(this.base+'/sanctum/csrf-cookie').then(response => {
                    axios.post(this.base+'/reset-password', this.formData).then(response => {
                        alert('passwordReset');
                        window.location.href = this.base
                    }).catch(error => {
                        this.error.password = error.response.data.password[0]
                    })
                })
            },

           
        }
    }
</script>
