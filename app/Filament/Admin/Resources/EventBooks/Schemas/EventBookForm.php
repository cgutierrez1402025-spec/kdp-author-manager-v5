<?php

namespace App\Filament\Admin\Resources\EventBooks\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class EventBookForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Evento y Libro')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->relationship('bookEvent', 'title')
                            ->label('Evento')
                            ->required(),

                        Forms\Components\Select::make('work_id')
                            ->relationship('work', 'title_public', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->where('user_id', auth()->id())
                            )
                            ->live()
                            ->afterStateUpdated(function ($set): void {
                                $set('edition_id', null);
                                $set('work_language_id', null);
                            })
                            ->label('Obra')
                            ->required(),

                        Forms\Components\Select::make('edition_id')
                            ->relationship('edition', 'edition_name', modifyQueryUsing: fn ($query, $get) => $query->when($get('work_id'), fn ($q, $workId) => $q->where('work_id', $workId))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->edition_name ?? "Edición {$record->edition_number}")
                            ->label('Edición'),

                        Forms\Components\Select::make('work_language_id')
                            ->relationship('workLanguage', 'language_code', modifyQueryUsing: fn ($query, $get) => $query->when($get('work_id'), fn ($q, $workId) => $q->where('work_id', $workId))
                            )
                            ->label('Idioma'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Inventario y Ventas')
                    ->schema([
                        Forms\Components\TextInput::make('copies_brought')
                            ->label('Copias Llevadas')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('copies_sold')
                            ->label('Copias Vendidas')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('copies_gifted')
                            ->label('Copias Regaladas')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('copies_returned')
                            ->label('Copias Devueltas')
                            ->numeric()
                            ->default(0),

                        Forms\Components\TextInput::make('unit_sale_price')
                            ->label('Precio Venta')
                            ->numeric()
                            ->step('0.01')
                            ->default(0),

                        Forms\Components\TextInput::make('income_amount')
                            ->label('Ingresos')
                            ->numeric()
                            ->step('0.01')
                            ->readOnly(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ]),
            ]);
    }
}
