<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('/products', \App\Http\Controllers\ProductController::class);
Route::resource('/supplier', \App\Http\Controllers\SupplierController::class);
Route::resource('/transaction', \App\Http\Controllers\TransactionController::class);