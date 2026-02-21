<?php

use App\Http\Controllers\BookingCheckController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::get('/bookings', [BookingController::class, 'index']);
Route::get('/bookings/check', [BookingCheckController::class, 'check']);
Route::post('/bookings', [BookingController::class, 'store']);
