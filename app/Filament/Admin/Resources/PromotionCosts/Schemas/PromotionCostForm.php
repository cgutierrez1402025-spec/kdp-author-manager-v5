<?php

namespace App\Filament\Admin\Resources\PromotionCosts\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class PromotionCostForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Costo de Promoción')
                    ->schema([
                        Forms\Components\Select::make('book_promotion_id')
                            ->relationship('bookPromotion', 'promotion_name', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->whereHas('publication.work', fn ($work) => $work->where('user_id', auth()->id()))
                            )
                            ->label('Promoción')
                            ->required(),

                        Forms\Components\Select::make('cost_type')
                            ->label('Tipo de Costo')
                            ->options([
                                'advertising' => 'Publicidad',
                                'promotion' => 'Promoción',
                                'marketing' => 'Marketing',
                                'tools' => 'Herramientas',
                                'other' => 'Otro',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('description')
                            ->label('Descripción')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('amount')
                            ->label('Importe')
                            ->numeric()
                            ->step('0.01')
                            ->required(),

                        Forms\Components\TextInput::make('currency')
                            ->label('Moneda')
                            ->maxLength(3)
                            ->default('EUR')
                            ->required(),

                        Forms\Components\DatePicker::make('date')
                            ->label('Fecha')
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ])
                    ->columns(2),
            ]);
    }
}
