<?php

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');

Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
