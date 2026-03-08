<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ProjectStats extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        if ($user->role === 'developer') {
            $myTasks = Task::query()->where('assigned_to', $user->id);

            return [
                Stat::make('My Projects', (clone $myTasks)->distinct('project_id')->count('project_id'))
                    ->description('Projects with tasks assigned to you')
                    ->descriptionIcon('heroicon-m-briefcase')
                    ->color('primary'),

                Stat::make('My Tasks', (clone $myTasks)->count())
                    ->description('Tasks assigned to you')
                    ->descriptionIcon('heroicon-m-clipboard-document-list'),

                Stat::make('My Completed Tasks', (clone $myTasks)->where('status', 'done')->count())
                    ->description('Tasks you finished')
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success'),
            ];
        }

        $projectQuery = Project::query();
        if ($user->role === 'manager') {
            $projectQuery->where('created_by', $user->id);
        }

        $taskQuery = Task::query();
        if ($user->role === 'manager') {
            $taskQuery->whereHas('project', fn($q) => $q->where('created_by', $user->id));
        }

        return [
            Stat::make('Total Projects', $projectQuery->count())
                ->description('Active projects in system')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),

            Stat::make('Total Tasks', $taskQuery->count())
                ->description('Tasks currently assigned')
                ->descriptionIcon('heroicon-m-clipboard-document-list'),

            Stat::make('Tasks Completed', (clone $taskQuery)->where('status', 'done')->count())
                ->description('Successfully finished')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
