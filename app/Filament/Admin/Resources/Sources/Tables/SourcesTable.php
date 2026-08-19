<?php

namespace App\Filament\Admin\Resources\Sources\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('work'))
            ->columns([
                TextColumn::make('work.title_public')
                    ->label('Obra')
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('author')
                    ->label('Autor')
                    ->searchable(),

                TextColumn::make('source_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'book' => 'primary',
                        'article' => 'info',
                        'website' => 'success',
                        'interview' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('language_code')
                    ->label('Idioma')
                    ->badge(),

                TextColumn::make('year')
                    ->label('Año')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('source_type')
                    ->label('Tipo')
                    ->options([
                        'book' => 'Libro',
                        'article' => 'Artículo',
                        'website' => 'Sitio Web',
                        'interview' => 'Entrevista',
                        'other' => 'Otro',
                    ]),
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
