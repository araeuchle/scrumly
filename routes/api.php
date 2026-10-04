<?php

use App\Http\Controllers\Api\OpenApiController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::apiResource('posts', PostController::class);
    Route::post('/posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
    Route::post('/posts/{post}/unpublish', [PostController::class, 'unpublish'])->name('posts.unpublish');

    Route::get('/openapi.yaml', [OpenApiController::class, 'show'])->name('openapi');
});
