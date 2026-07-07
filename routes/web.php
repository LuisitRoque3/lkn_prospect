<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/track/{uuid}', [\App\Http\Controllers\TrackingController::class, 'track'])->name('tracking.pixel');
