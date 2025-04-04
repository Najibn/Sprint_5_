<?php

use App\Http\Controllers\Api\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

*/


Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login',    [UserAuthController::class, 'login']);
Route::post('/logout',   [UserAuthController::class, 'logout']);