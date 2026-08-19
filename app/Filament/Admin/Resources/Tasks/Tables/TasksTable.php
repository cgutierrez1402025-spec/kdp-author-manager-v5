<?php

namespace App\Filament\Admin\Resources\Tasks\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['work', 'assignedTo']))
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('work.title_public')
                    ->label('Obra')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('assignedTo.name')
                    ->label('Asignado a')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Sin asignar'),

                TextColumn::make('priority')
                    ->label('Prioridad')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente', default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'urgent' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Pendiente', 'in_progress' => 'En progreso', 'completed' => 'Completada', 'cancelled' => 'Cancelada', default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('due_date')
                    ->label('Vencimiento')
                    ->date()
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'in_progress' => 'En Progreso',
                        'completed' => 'Completada',
                    ]),

                SelectFilter::make('assigned_to')
                    ->label('Asignado a')
                    ->relationship('assignedTo', 'name'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->emptyStateHeading('No hay tareas')
            ->emptyStateDescription('Crea una tarea para convertir el próximo paso en una acción concreta.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
