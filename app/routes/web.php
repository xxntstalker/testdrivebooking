<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;

Route::get('/', [CustomerController::class, 'home'])->name('customer.home');
Route::get('/booking', [CustomerController::class, 'booking'])->name('customer.booking');
Route::post('/booking', [CustomerController::class, 'store'])->name('customer.store');
