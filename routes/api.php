<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductCategory;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/users', UserController::class);

    Route::apiResource('/product-categories', ProductCategory::class);

    Route::apiResource('/products', \App\Http\Controllers\Api\ProductController::class);

    Route::apiResource('/stock-movements', \App\Http\Controllers\Api\StockMovement::class);

    Route::apiResource('/suppliers', \App\Http\Controllers\Api\SuppliersController::class);

    Route::apiResource('/purchases', \App\Http\Controllers\Api\PurchaseController::class);

    Route::apiResource('/purchase-items', \App\Http\Controllers\Api\PurchaseItemController::class);

    Route::post('/logout', [AuthController::class, 'logout']);
});
