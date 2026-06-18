<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BedController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::prefix('search')->name('search.')->group(function () {
    Route::get('/', SearchController::class)->name('index');
});

Route::prefix('beds')->name('beds.')->group(function () {
    Route::get('/{bed}', [BedController::class, 'show'])->name('show');
});

Route::middleware('guest')->prefix('auth')->name('auth.')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [LoginController::class, 'destroy'])->name('auth.logout');
});
