<?php

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
