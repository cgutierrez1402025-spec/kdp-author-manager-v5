<?php

namespace App\Filament\Admin\Resources\PromotionDailyResults\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class PromotionDailyResultForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Resultado Diario')
                    ->schema([
                        Forms\Components\Select::make('book_promotion_id')
                            ->relationship('bookPromotion', 'promotion_name', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->whereHas('publication.work', fn ($work) => $work->where('user_id', auth()->id()))
                            )
                            ->label('Promoción')
                            ->required(),

                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha')
                            ->required(),

                        Forms\Components\TextInput::make('paid_units')
                            ->label('Unidades Pagadas')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('free_units_promo')
                            ->label('Unidades Gratis (Promo)')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('free_units_price_match')
                            ->label('Unidades Gratis (Price Match)')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('kenp_pages_read')
                            ->label('Páginas KENP')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('gross_royalties')
                            ->label('Royalties Brutas')
                            ->numeric()
                            ->step('0.01')
                            ->default(0),

                        Forms\Components\TextInput::make('net_royalties')
                            ->label('Royalties Netas')
                            ->numeric()
                            ->step('0.01')
                            ->default(0),

                        Forms\Components\TextInput::make('currency')
                            ->label('Moneda')
                            ->maxLength(3)
                            ->default('EUR'),

                        Forms\Components\TextInput::make('ranking_position')
                            ->label('Posición en Ranking')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ])
                    ->columns(3),
            ]);
    }
}
