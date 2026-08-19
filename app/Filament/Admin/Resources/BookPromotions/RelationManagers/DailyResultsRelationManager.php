<?php

namespace App\Filament\Admin\Resources\BookPromotions\RelationManagers;

use App\Models\PromotionDailyResult;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DailyResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'dailyResults';  // ✅ Corregido: quitado el ?

    protected static ?string $recordTitleAttribute = 'date';

    protected static ?string $title = 'Resultados Diarios';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Fecha')
                    ->required(),

                TextInput::make('paid_units')
                    ->label('Unidades Pagadas')
                    ->numeric()
                    ->default(0),

                TextInput::make('free_units_promo')
                    ->label('Unidades Gratis (Promo)')
                    ->numeric()
                    ->default(0),

                TextInput::make('free_units_price_match')
                    ->label('Unidades Gratis (Price Match)')
                    ->numeric()
                    ->default(0),

                TextInput::make('kenp_pages_read')
                    ->label('Páginas KENP')
                    ->numeric()
                    ->default(0),

                TextInput::make('gross_royalties')
                    ->label('Royalties Brutas')
                    ->numeric()
                    ->step('0.01')
                    ->default(0),

                TextInput::make('net_royalties')
                    ->label('Royalties Netas')
                    ->numeric()
                    ->step('0.01')
                    ->default(0),

                TextInput::make('currency')
                    ->label('Moneda')
                    ->maxLength(3)
                    ->default('EUR'),

                TextInput::make('ranking_position')
                    ->label('Posición Ranking')
                    ->numeric()
                    ->nullable(),

                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                TextColumn::make('total_units')
                    ->label('Total Unidades')
                    ->state(fn (PromotionDailyResult $record) => $record->paid_units + $record->free_units_promo + $record->free_units_price_match),

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
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
