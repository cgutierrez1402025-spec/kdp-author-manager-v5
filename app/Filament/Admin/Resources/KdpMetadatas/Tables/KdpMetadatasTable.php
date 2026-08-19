<?php

namespace App\Filament\Admin\Resources\KdpMetadatas\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KdpMetadatasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('publication.work.title_public')
                    ->label('Obra')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),

                TextColumn::make('author')
                    ->label('Autor')
                    ->searchable(),

                TextColumn::make('series_name')
                    ->label('Serie')
                    ->searchable(),
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
