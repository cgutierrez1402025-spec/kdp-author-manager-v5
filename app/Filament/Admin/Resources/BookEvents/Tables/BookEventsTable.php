<?php

namespace App\Filament\Admin\Resources\BookEvents\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event_type')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('event_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                TextColumn::make('location_name')
                    ->label('Lugar')
                    ->searchable(),

                TextColumn::make('total_copies_sold')
                    ->label('Copias Vendidas'),

                TextColumn::make('total_income')
                    ->label('Ingresos')
                    ->money('EUR'),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'planned' => 'gray',
                        'confirmed' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Tipo')
                    ->options([
                        'book_fair' => 'Feria',
                        'signing' => 'Firma',
                        'presentation' => 'Presentación',
                        'conference' => 'Conferencia',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'planned' => 'Planeado',
                        'confirmed' => 'Confirmado',
                        'completed' => 'Completado',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
