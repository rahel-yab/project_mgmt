<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'developer'], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'manager') {
            return (int) optional($task->project)->created_by === (int) $user->id;
        }

        return (int) $task->assigned_to === (int) $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager'], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'manager') {
            return (int) optional($task->project)->created_by === (int) $user->id;
        }

        return (int) $task->assigned_to === (int) $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return $this->update($user, $task);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'manager' && (int) optional($task->project)->created_by === (int) $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }
}
