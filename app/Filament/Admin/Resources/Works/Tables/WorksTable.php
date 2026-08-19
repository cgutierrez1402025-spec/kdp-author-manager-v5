<?php

namespace App\Filament\Admin\Resources\Works\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['series', 'user', 'publications']))
            ->columns([
                TextColumn::make('title_public')
                    ->label('Título Público')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('author_name')
                    ->label('Autor')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('series.title')
                    ->label('Serie')
                    ->toggleable()
                    ->sortable(),

                TextColumn::make('series_number')
                    ->label('Número en Serie')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('genre')
                    ->label('Género')
                    ->toggleable()
                    ->badge(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'idea' => 'Idea',
                        'redaccion' => 'Redacción',
                        'revision' => 'Revisión',
                        'preparacion' => 'Preparación',
                        'publicada' => 'Publicada',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'idea' => 'gray',
                        'redaccion' => 'warning',
                        'revision' => 'info',
                        'preparacion' => 'primary',
                        'publicada' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('original_language')
                    ->label('Idioma')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->since()
                    ->toggleable()
                    ->sortable(),
            ])
            ->filtersFormColumns(3)
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'idea' => 'Idea',
                        'redaccion' => 'Redacción',
                        'revision' => 'Revisión',
                        'preparacion' => 'Preparación',
                        'publicada' => 'Publicada',
                    ]),

                SelectFilter::make('genre')
                    ->label('Género')
                    ->options([
                        'ficcion' => 'Ficción',
                        'novela' => 'Novela',
                        'poesia' => 'Poesía',
                        'cuento' => 'Cuento',
                        'infantil' => 'Infantil',
                        'juvenil' => 'Juvenil',
                        'romance' => 'Romance',
                        'ciencia_ficcion' => 'Ciencia Ficción',
                        'fantasia' => 'Fantasía',
                        'misterio' => 'Misterio',
                        'thriller' => 'Thriller',
                        'otra' => 'Otra',
                    ]),

                SelectFilter::make('original_language')
                    ->label('Idioma Original')
                    ->options([
                        'es' => 'Español',
                        'en' => 'Inglés',
                        'fr' => 'Francés',
                        'de' => 'Alemán',
                        'it' => 'Italiano',
                        'pt' => 'Portugués',
                        'ru' => 'Ruso',
                        'ja' => 'Japonés',
                        'zh' => 'Chino',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->emptyStateHeading('Todavía no hay obras')
            ->emptyStateDescription('Crea tu primera obra para organizar manuscritos, publicaciones y marketing.')
            ->emptyStateIcon('heroicon-o-book-open')
            ->defaultSort('created_at', 'desc');
    }
}
