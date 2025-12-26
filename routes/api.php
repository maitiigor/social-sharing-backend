<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/auth/register', [App\Http\Controllers\AuthController::class, 'register']);

Route::get('image/{image}', [\App\Http\Controllers\ImageController::class, 'show'])->name('image.show');

Route::middleware('auth:sanctum')->group(function () {
    // Users
    Route::post('/auth/logout', [App\Http\Controllers\AuthController::class, 'logout']);
    Route::apiResource('/users', App\Http\Controllers\UserController::class);
    Route::get('/user/search', [App\Http\Controllers\UserController::class, 'search']);
    Route::get('/my-posts', [App\Http\Controllers\PostController::class, 'getMyPosts']);
    Route::post('/posts/{post}/comments', [App\Http\Controllers\PostController::class, 'saveComment']);
    Route::get('/posts/{post}/comments', [App\Http\Controllers\PostController::class, 'getComments']);
    Route::get('post/search', [App\Http\Controllers\PostController::class, 'search']);  // TODO: add middleware for this route ('can:viewAny,Post)')
    Route::get('/comments/{comment}/child-comments', [App\Http\Controllers\PostController::class, 'getChildComments']);
    Route::post('/posts/{post}/rate', [App\Http\Controllers\PostController::class, 'saveRating']);
    Route::post('/posts/{post}/like', [App\Http\Controllers\PostController::class, 'saveLike']);
    Route::delete('/posts/{post}/like', [App\Http\Controllers\PostController::class, 'removeLike']);
    Route::get('/posts/{post}/user-status', [App\Http\Controllers\PostController::class, 'getUserPhotoStatus']);
    Route::get('/posts/liked', [App\Http\Controllers\PostController::class, 'getLikedPosts']);
    Route::apiResource('/posts', App\Http\Controllers\PostController::class);
    Route::apiResource('/comments', App\Http\Controllers\CommentController::class);
    Route::apiResource('/images', App\Http\Controllers\ImageController::class);
});

