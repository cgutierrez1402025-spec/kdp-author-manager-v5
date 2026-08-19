<?php

namespace App\Filament\Admin\Resources\KdpSelectPeriods\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class KdpSelectPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('publication.work'))
            ->columns([
                TextColumn::make('publication.work.title_public')
                    ->label('Obra')
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label('Inicio')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date()
                    ->sortable(),

                TextColumn::make('free_promo_days_remaining')
                    ->label('Días Restantes')
                    ->numeric(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('auto_renewal')
                    ->label('Auto Renovación')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Activo',
                        'expired' => 'Expirado',
                        'cancelled' => 'Cancelado',
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
