<?php

namespace App\Filament\Admin\Resources\BookPromotions\Schemas;

use App\Models\Publication;
use Filament\Forms;
use Filament\Forms\Form;

class BookPromotionForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Promoción')
                    ->schema([
                        Forms\Components\Select::make('publication_id')
                            ->relationship('publication', 'asin', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->whereHas('work', fn ($work) => $work->where('user_id', auth()->id()))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => ($record->work?->title_public ?? 'Publicación').' · '.($record->asin ?? "#{$record->id}")
                            )
                            ->live()
                            ->afterStateUpdated(function ($set): void {
                                $set('marketplace_id', null);
                                $set('kdp_select_period_id', null);
                            })
                            ->label('Publicación')
                            ->required(),

                        Forms\Components\Select::make('marketplace_id')
                            ->relationship('marketplace', 'name', modifyQueryUsing: function ($query, $get) {
                                $platformId = $get('publication_id') ? Publication::find($get('publication_id'))?->platform_id : null;

                                return $query->when($platformId, fn ($q) => $q->where('platform_id', $platformId));
                            })
                            ->label('Marketplace')
                            ->nullable(),

                        Forms\Components\Select::make('kdp_select_period_id')
                            ->relationship('kdpSelectPeriod', 'id', modifyQueryUsing: fn ($query, $get) => $query->when($get('publication_id'), fn ($q, $publicationId) => $q->where('publication_id', $publicationId))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => 'KDP Select · '.$record->start_date?->format('d/m/Y').' — '.$record->end_date?->format('d/m/Y')
                            )
                            ->label('Período KDP Select')
                            ->nullable(),

                        Forms\Components\Select::make('promotion_type')
                            ->label('Tipo de Promoción')
                            ->options([
                                'free' => 'Gratis',
                                'kindle_countdown' => 'Kindle Countdown',
                                'price_promo' => 'Precio Promocional',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('promotion_name')
                            ->label('Nombre de Promoción')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Fechas y Precios')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha Inicio')
                            ->required(),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Fecha Fin')
                            ->required(),

                        Forms\Components\TextInput::make('normal_price')
                            ->label('Precio Normal')
                            ->numeric()
                            ->step('0.01'),

                        Forms\Components\TextInput::make('promotional_price')
                            ->label('Precio Promocional')
                            ->numeric()
                            ->step('0.01'),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'planned' => 'Planeada',
                                'active' => 'Activa',
                                'completed' => 'Completada',
                                'cancelled' => 'Cancelada',
                            ])
                            ->default('planned')
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\Textarea::make('objective')
                            ->label('Objetivo')
                            ->rows(3),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ]),
            ]);
    }
}
