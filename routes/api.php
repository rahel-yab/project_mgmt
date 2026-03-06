<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    // --- PROJECT ROUTES ---
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store'])->middleware('role:manager');
    Route::get('/projects/{project}', [ProjectController::class, 'show']); 
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);

    // --- TASK ROUTES ---
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store'])->middleware('role:manager');
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    // Status update (The one we protected with a Policy)
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // --- COMMENT ROUTES ---
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    
});