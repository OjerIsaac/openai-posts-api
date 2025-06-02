<?php

use App\Http\Controllers\Common\PostController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts', [PostController::class, 'index']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
});
