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

Route::get('/login', function () {
    return view('login');
})->name('login'); 

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/user-master', function () {
    return view('user-master');
}); 


Route::get('/project-details', function () {
    return view('project-details');
})->name('project-details');