<?php

namespace App\Filament\Widgets;

use App\Enums\TaskStatus;
use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class MyAssignedTasksWidget extends BaseWidget
{
    protected static ?string $heading = 'My Assigned Tasks';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->role === 'developer';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable(),
                BadgeColumn::make('priority')
                    ->colors([
                        'success' => 'low',
                        'warning' => 'medium',
                        'danger' => 'high',
                    ]),
                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'todo',
                        'warning' => 'in_progress',
                        'success' => 'done',
                    ]),
            ])
            ->actions([
                Action::make('open_task')
                    ->label('Open Task')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Task $record): string => TaskResource::getUrl('edit', ['record' => $record])),
                Action::make('update_status')
                    ->label('Update Status')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        Select::make('status')
                            ->label('Status')
                            ->options(TaskStatus::options())
                            ->required(),
                    ])
                    ->fillForm(fn (Task $record): array => [
                        'status' => $record->status,
                    ])
                    ->action(function (Task $record, array $data): void {
                        abort_unless(Gate::allows('update', $record), 403);

                        $record->update([
                            'status' => $data['status'],
                        ]);

                        Notification::make()
                            ->title('Task status updated')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([5, 10, 25]);
    }

    protected function getTableQuery(): Builder
    {
        return Task::query()->where('assigned_to', Auth::id());
    }
}
