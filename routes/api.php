<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

Route::get('products', ProductController::class)->name('products.index');
Route::post('/orders', OrderController::class)->name('orders.store');
