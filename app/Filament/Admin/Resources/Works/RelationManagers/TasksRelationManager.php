<?php

namespace App\Filament\Admin\Resources\Works\RelationManagers;

use App\Filament\Admin\Resources\Tasks\TaskResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    protected static ?string $title = 'Tareas';

    protected static ?string $icon = 'heroicon-o-check-circle';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Tarea')->searchable()->sortable(),
                TextColumn::make('priority')->label('Prioridad')->badge()->sortable(),
                TextColumn::make('status')->label('Estado')->badge()->sortable(),
                TextColumn::make('due_date')->label('Vencimiento')->date()->sortable(),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nueva tarea')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => TaskResource::getUrl('create', ['work_id' => $this->getOwnerRecord()->getKey()])),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Abrir')
                    ->url(fn ($record): string => TaskResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('No hay tareas para esta obra')
            ->emptyStateIcon('heroicon-o-check-badge');
    }
}
