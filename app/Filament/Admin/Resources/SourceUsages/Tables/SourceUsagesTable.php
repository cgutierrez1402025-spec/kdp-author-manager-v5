<?php

namespace App\Filament\Admin\Resources\SourceUsages\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SourceUsagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['source', 'work', 'manuscriptVersion', 'chapter']))
            ->columns([
                TextColumn::make('source.title')
                    ->label('Fuente')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('work.title_public')
                    ->label('Obra')
                    ->searchable(),

                TextColumn::make('manuscriptVersion.version_number')
                    ->label('Versión')
                    ->searchable(),

                TextColumn::make('chapter.title')
                    ->label('Capítulo')
                    ->searchable(),

                TextColumn::make('usage_type')
                    ->label('Tipo')
                    ->searchable(),

                ToggleColumn::make('verified')
                    ->label('Verificado'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
