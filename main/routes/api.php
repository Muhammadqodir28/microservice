<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/chat/messages', [ChatMessageController::class, 'index']);
    Route::post('/chat/messages', [ChatMessageController::class, 'store'])->middleware('throttle:30,1');
    Route::get('/user', [UserController::class, 'current']);
    Route::get('/user/detail', [UserController::class, 'detail']);
    Route::get('/profile', [UserController::class, 'show']);
    Route::patch('/profile', [UserController::class, 'update']);
    Route::put('/profile/password', [UserController::class, 'updatePassword']);
    Route::get('/profile/sessions', [UserController::class, 'sessions']);
    Route::delete('/profile/sessions/{tokenId}', [UserController::class, 'revokeSession']);
    Route::post('/profile/sessions/revoke-others', [UserController::class, 'revokeOtherSessions']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/products', [ProductController::class, 'index']);
Route::middleware('auth:api')->post('/products/{id}/like', [ProductController::class, 'like']);
