<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('/auth/web3/authenticate', \App\Http\Controllers\Web3AuthController::class);

Route::get('/dashboard', function () {
    return view('welcome');
});
Route::get('/home', function () {
    return view('welcome');
});
