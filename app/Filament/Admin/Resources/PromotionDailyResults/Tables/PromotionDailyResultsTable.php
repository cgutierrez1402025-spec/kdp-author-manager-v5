<?php

namespace App\Filament\Admin\Resources\PromotionDailyResults\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromotionDailyResultsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('bookPromotion'))
            ->columns([
                TextColumn::make('bookPromotion.promotion_name')
                    ->label('Promoción')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                TextColumn::make('total_units')
                    ->label('Total Unidades')
                    ->state(fn ($record) => $record->paid_units + $record->free_units_promo + $record->free_units_price_match),

                TextColumn::make('paid_units')
                    ->label('Pagadas')
                    ->sortable(),

                TextColumn::make('free_units_promo')
                    ->label('Gratis')
                    ->sortable(),

                TextColumn::make('net_royalties')
                    ->label('Royalties Netas')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('ranking_position')
                    ->label('Ranking')
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
