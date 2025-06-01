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

    Route::apiResource('/customers', \App\Http\Controllers\Api\CustomersController::class);

    Route::apiResource('/sales', \App\Http\Controllers\Api\SalesController::class);

    Route::apiResource('/sales-items', \App\Http\Controllers\Api\SalesItemController::class);

    Route::apiResource('/employee', \App\Http\Controllers\Api\EmployeesController::class);

    Route::apiResource('/attendance', \App\Http\Controllers\Api\AttendancesController::class);

    Route::apiResource('/payroll', \App\Http\Controllers\Api\PayrollController::class);

    Route::get('/dashboard', [\App\Http\Controllers\Api\DashboardController::class, 'getDashboard']);

    Route::get('/dashboard-admin', [\App\Http\Controllers\Api\DashboardController::class, 'getDashboardAdmin']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
