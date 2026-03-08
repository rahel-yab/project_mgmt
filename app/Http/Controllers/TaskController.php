<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Models\Task;
use App\Services\TaskService;
use App\Http\Resources\TaskResource; // 1. Import the Resource
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(protected TaskService $taskService) {}

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskService->createTask($request->validated());

        return (new TaskResource($task))
            ->additional(['message' => 'Task created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        Gate::authorize('updateStatus', $task);

        $updatedTask = $this->taskService->updateStatus($task, $request->validated('status'));
    
        return (new TaskResource($updatedTask))
            ->additional(['message' => 'Status updated successfully']);
    }

    public function index()
    {
        $tasks = $this->taskService->getAllTasks();
        
        return TaskResource::collection($tasks);
    }

    public function destroy(Task $task)
{
    // Check if the user is authorized to delete (Usually Managers/Admin)
    Gate::authorize('delete', $task);

    $this->taskService->deleteTask($task);

    return response()->json(['message' => 'Task moved to trash (Soft Deleted)']);
}
}
