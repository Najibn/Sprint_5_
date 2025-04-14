<?php

use App\Http\Controllers\Api\UserAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

*/

Route::post('/register', [UserAuthController::class, 'register']);
Route::post('/login',    [UserAuthController::class, 'login']);

//Protected routes
Route::middleware('auth:api')->group(function () {

    Route::post('/refreshToken', [UserAuthController::class, 'refreshToken']);
    Route::post('/logout',       [UserAuthController::class, 'logout']);
    

     Route::get('/user', function (Request $request) {
        return $request->user();
    });

});


