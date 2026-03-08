<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
{
    $user = Auth::user();
    $query = parent::getEloquentQuery();

    if ($user->role === 'developer') {
        return $query->where('assigned_to', $user->id);
    }
    if ($user->role === 'manager') {
        return $query->whereHas('project', fn($q) => $q->where('created_by', $user->id));
    }
    return $query;
}

public static function form(Form $form): Form
{
    $isDev = Auth::user()->role === 'developer';

    return $form->schema([
        Forms\Components\Select::make('project_id')
            ->relationship('project', 'name', fn($query) => 
                Auth::user()->role === 'manager' 
                ? $query->where('created_by', Auth::id()) 
                : $query
            )
            ->disabled($isDev)->required(),
        Forms\Components\TextInput::make('title')->disabled($isDev)->required(),
        Forms\Components\Select::make('assigned_to')
            ->relationship('developer', 'name')
            ->disabled($isDev)->required(),
        Forms\Components\Select::make('priority')
            ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
            ->disabled($isDev)->required(),
        Forms\Components\Select::make('status')
            ->options(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'])
            ->required(),
        Forms\Components\Textarea::make('description')->disabled($isDev),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('project.name')->sortable(),
            Tables\Columns\TextColumn::make('developer.name')->label('Assigned To'),
            Tables\Columns\SelectColumn::make('status') // Quick update for Devs
                ->options(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done']),
            Tables\Columns\TextColumn::make('priority')->badge(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->label('Status')
                ->options(['todo' => 'To Do', 'in_progress' => 'In Progress', 'done' => 'Done'])
                ->visible(fn () => Auth::user()?->role === 'admin'),
            Tables\Filters\SelectFilter::make('priority')
                ->label('Priority')
                ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'])
                ->visible(fn () => Auth::user()?->role === 'admin'),
            Tables\Filters\SelectFilter::make('assigned_to')
                ->label('Assigned User')
                ->relationship('developer', 'name', fn (Builder $query) => $query->where('role', 'developer'))
                ->searchable()
                ->preload()
                ->visible(fn () => Auth::user()?->role === 'admin'),
        ]);
}
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
