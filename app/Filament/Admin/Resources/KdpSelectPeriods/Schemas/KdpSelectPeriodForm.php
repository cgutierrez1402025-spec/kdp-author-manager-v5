<?php

namespace App\Filament\Admin\Resources\KdpSelectPeriods\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class KdpSelectPeriodForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Período')
                    ->schema([
                        Forms\Components\Select::make('publication_id')
                            ->relationship('publication', 'asin', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->whereHas('work', fn ($work) => $work->where('user_id', auth()->id()))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => ($record->work?->title_public ?? 'Publicación').' · '.($record->asin ?? "#{$record->id}")
                            )
                            ->label('Publicación')
                            ->required(),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha Inicio')
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Fecha Fin')
                            ->required(),

                        Forms\Components\Toggle::make('auto_renewal')
                            ->label('Renovación Automática')
                            ->default(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Días de Promoción')
                    ->schema([
                        Forms\Components\TextInput::make('free_promo_days_allowed')
                            ->label('Días Permitidos')
                            ->numeric()
                            ->default(5)
                            ->minValue(0)
                            ->maxValue(5),

                        Forms\Components\TextInput::make('free_promo_days_used')
                            ->label('Días Usados')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('free_promo_days_remaining')
                            ->label('Días Restantes')
                            ->numeric()
                            ->default(5)
                            ->minValue(0),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Activo',
                                'expired' => 'Expirado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ]),
            ]);
    }
}
