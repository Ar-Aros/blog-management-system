<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'index']); // Guests can view feed
Route::get('/posts/{id}', [PostController::class, 'show']); // View single post

// Protected Routes (Require Login)
Route::group(['middleware' => ['auth:sanctum']], function () {
    
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    
    Route::post('/posts/{id}/bookmark', [PostController::class, 'bookmarkOrUnbookmark']);
    
    // Interactions
    Route::post('/posts/{id}/like', [PostController::class, 'likeOrUnlike']);

    Route::post('/posts/{id}/comments', [CommentController::class, 'store']); // Create comment
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);   // Delete comment
});