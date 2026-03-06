<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    public function store(Request $request) 
    {
        // Validation here (or in a Form Request)
        $task = $this->taskService->createTask($request->all());
        return response()->json($task, 201);
    }

    public function updateStatus(Request $request, Task $task)
    {
        // Check the Policy we made in Step 1.1
        // If it fails, Laravel returns 403 Forbidden automatically
        Gate::authorize('updateStatus', $task);

        $updatedTask = $this->taskService->updateStatus($task, $request->status);
        
        return response()->json([
            'message' => 'Status updated',
            'task' => $updatedTask
        ]);
    }
}