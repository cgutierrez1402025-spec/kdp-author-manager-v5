<?php

namespace App\Filament\Admin\Resources\EventBooks\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventBooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['bookEvent', 'work', 'edition']))
            ->columns([
                TextColumn::make('bookEvent.title')
                    ->label('Evento')
                    ->searchable(),

                TextColumn::make('work.title_public')
                    ->label('Obra')
                    ->searchable(),

                TextColumn::make('copies_brought')
                    ->label('Llevadas')
                    ->sortable(),

                TextColumn::make('copies_sold')
                    ->label('Vendidas')
                    ->sortable(),

                TextColumn::make('income_amount')
                    ->label('Ingresos')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
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
