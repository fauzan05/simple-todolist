<?php

use App\Http\Controllers\Auth\DoodleController;
use App\Http\Controllers\Auth\OauthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Auth;
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

Auth::routes();

Route::get('/auth/doodle', [DoodleController::class, 'redirect']);
Route::get('/auth/doodle/callback', [DoodleController::class, 'callback']);
Route::middleware('auth')->group(function() {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/auth/doodle/refresh', [DoodleController::class, 'refreshToken']);
});

