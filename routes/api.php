<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\ProductController;

Route::name('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::name('password.')->group(function () {
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('email');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('update');
});

Route::name('categories.')->prefix('categories')->group(function () {
    Route::post('/create', [CategoryController::class, 'create'])->name('create');
    Route::get('/read/{categoryId}', [CategoryController::class, 'read'])->name('read');
    Route::post('/update/{categoryId}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/delete/{categoryId}', [CategoryController::class, 'delete'])->name('delete');
});

Route::name("products.")->prefix('products')->group(function () {
    Route::post('/create', [ProductController::class, 'create'])->name('create');
    Route::get('/read/{productId}', [ProductController::class, 'read'])->name('read');
    Route::post('/update/{productId}', [ProductController::class, 'update'])->name('update');
    Route::delete('/delete/{productId}', [ProductController::class, 'delete'])->name('delete');
});

Route::name('cart.')->prefix('cart')->group(function () {
    Route::post('/add-product', [CartController::class, 'addProduct'])->name('add-product');
    Route::delete('/remove-product/{productId}', [CartController::class, 'removeProduct'])->name('remove-product');
    Route::post('/increase-quantity', [CartController::class, 'increaseQuantity'])->name('increase-quantity');
    Route::post('/decrease-quantity', [CartController::class, 'decreaseQuantity'])->name('decrease-quantity');

    Route::post('/make-order', [CartController::class, 'makeOrder'])->name('make-order');
});
