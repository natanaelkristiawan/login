<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'PublicController@index')->name('public.index');

Route::get('google-login', 'PublicController@googleLogin')->name('public.googleLogin');
Route::get('google-connect', 'PublicController@googleConnect'); 

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
