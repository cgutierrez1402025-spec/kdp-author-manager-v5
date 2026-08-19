<?php

namespace App\Filament\Admin\Resources\Publications\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PublicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['work', 'platform', 'marketplace', 'workLanguage', 'manuscriptVersion']))
            ->columns([
                TextColumn::make('work.title_public')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('format')
                    ->label('Formato')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'paperback' => 'primary',
                        'hardcover' => 'success',
                        'kindle' => 'warning',
                        'audiobook' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('platform.name')
                    ->label('Plataforma')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('marketplace.name')
                    ->label('Marketplace')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Borrador', 'processing' => 'Procesando', 'published' => 'Publicada', 'error' => 'Error', default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'draft' => 'gray',
                        'processing' => 'warning',
                        'published' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label('Publicado')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('format')
                    ->label('Formato')
                    ->options([
                        'paperback' => 'Paperback',
                        'hardcover' => 'Hardcover',
                        'kindle' => 'Kindle',
                        'audiobook' => 'Audiobook',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'processing' => 'Procesando',
                        'published' => 'Publicado',
                        'error' => 'Error',
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
            ->striped()
            ->emptyStateHeading('No hay publicaciones')
            ->emptyStateDescription('Crea una edición cuando el manuscrito esté listo para su distribución.')
            ->emptyStateIcon('heroicon-o-globe-alt')
            ->defaultSort('created_at', 'desc');
    }
}
