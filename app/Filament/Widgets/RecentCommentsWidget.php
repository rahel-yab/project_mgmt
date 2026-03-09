<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RecentCommentsWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Comments';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()?->role !== 'developer';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('content')
                    ->label('Comment')
                    ->wrap()
                    ->limit(120)
                    ->searchable(),
                TextColumn::make('task.title')
                    ->label('Task')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Author')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Posted')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25]);
    }

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();

        $query = Comment::query()->with(['task', 'user']);

        if ($user->role === 'manager') {
            return $query->whereHas('task.project', fn (Builder $builder) => $builder->where('created_by', $user->id));
        }

        if ($user->role === 'developer') {
            return $query->whereHas('task', fn (Builder $builder) => $builder->where('assigned_to', $user->id));
        }

        return $query;
    }
}
