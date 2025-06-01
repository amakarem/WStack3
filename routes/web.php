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

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Auth::routes();

Route::post('/auth/web3/authenticate', \App\Http\Controllers\Web3AuthController::class);
Route::post('/web3/wallet/{address}', [App\Http\Controllers\API1inch::class, 'wallet']);
Route::post('/web3/getswapquote', [App\Http\Controllers\API1inch::class, 'getswapquote']);
Route::post('/web3/swapnow', [App\Http\Controllers\API1inch::class, 'swapnow']);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');

Route::get('/', function () {
    return view('home');
});

Route::get('/modal/swap', function () {
    return view('model.swap');
});

