<?php
namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskService
{
    public function createTask(array $data): Task
    {
        return Task::create($data);
    }

    public function updateStatus(Task $task, string $status): Task
    {
        $task->update(['status' => $status]);
        return $task;
    }
    public function getAllTasks(): LengthAwarePaginator
    {
        $user = Auth::user();
        $query = Task::with(['project', 'developer']);

        // Logic: If user is developer, only show their assigned tasks
        if ($user->role === 'developer') {
            $query->where('assigned_to', $user->id);
        }

        // Return paginated results (Requirement: Part 2 API)
        return $query->paginate(10);
    }

    public function deleteTask(Task $task): bool
{
    // Because the Task model uses the SoftDeletes trait, 
    // this won't erase the row, just set the deleted_at column.
    return $task->delete();
}
}