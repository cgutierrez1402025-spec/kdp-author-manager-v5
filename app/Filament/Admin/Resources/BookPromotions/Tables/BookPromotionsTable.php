<?php

namespace App\Filament\Admin\Resources\BookPromotions\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BookPromotionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['publication.work', 'marketplace', 'kdpSelectPeriod']))
            ->columns([
                TextColumn::make('publication.work.title_public')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('marketplace.name')
                    ->label('Marketplace')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('promotion_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'free' => 'success',
                        'kindle_countdown' => 'warning',
                        'price_promo' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('promotion_name')
                    ->label('Nombre')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'planned' => 'gray',
                        'active' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('promotion_type')
                    ->label('Tipo')
                    ->options([
                        'free' => 'Gratis',
                        'kindle_countdown' => 'Kindle Countdown',
                        'price_promo' => 'Precio Promocional',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'planned' => 'Planeada',
                        'active' => 'Activa',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
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
            ->defaultSort('start_date', 'desc');
    }
}
