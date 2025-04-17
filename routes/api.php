<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\MaintenanceRecordController;
use App\Http\Controllers\Api\UserAuthController;

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login',    [UserAuthController::class, 'login']);

//Protected routes
Route::middleware('auth:api')->group(function () {

    // Authentication routes
    Route::post('/refreshToken', [UserAuthController::class, 'refreshToken']);
    Route::post('/logout',       [UserAuthController::class, 'logout']);
    

    // User management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('products', ProductController::class);
        Route::apiResource('maintenance_records', MaintenanceRecordController::class);
    });


    // Customer routes
    Route::middleware(['role:customer'])->prefix('customer')->group(function () {
        Route::get('products', [CustomerController::class, 'getProducts']);
        Route::get('products/{id}', [CustomerController::class, 'getProduct']);
        Route::get('products/{id}/maintenance_records', [CustomerController::class, 'getProductMaintenanceRecords']);
        Route::put('products/{id}', [CustomerController::class, 'updateProduct']);
    });



    // Technician routes
    Route::middleware(['role:technician'])->prefix('technician')->group(function () {
        Route::get('assigned_products', [TechnicianController::class, 'getAssignedProducts']);
        Route::get('maintenance_records', [TechnicianController::class, 'getMaintenanceRecords']);
        Route::put('maintenance_records/{maintenanceRecord}', [TechnicianController::class, 'updateMaintenanceRecord']);
        Route::get('products/{id}', [TechnicianController::class, 'getProduct']);
        Route::get('products/{id}/maintenance-history', [TechnicianController::class, 'getProductMaintenanceHistory']);
    });



   //user profiles
     Route::get('/user', function (Request $request) {
        return $request->user();
    });

});


