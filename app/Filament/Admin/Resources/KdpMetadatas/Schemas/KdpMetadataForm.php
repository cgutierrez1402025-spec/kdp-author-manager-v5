<?php

namespace App\Filament\Admin\Resources\KdpMetadatas\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class KdpMetadataForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('publication_id')
                            ->relationship('publication', 'id', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->whereHas('work', fn ($work) => $work->where('user_id', auth()->id()))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => ($record->work?->title_public ?? 'Publicación').' · '.($record->asin ?? "#{$record->id}")
                            )
                            ->label('Publicación')
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtítulo')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('author')
                            ->label('Autor')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('contributors')
                            ->label('Contribuyentes')
                            ->helperText('JSON array format')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Serie y Descripción')
                    ->schema([
                        Forms\Components\TextInput::make('series_name')
                            ->label('Nombre de Serie')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('series_number')
                            ->label('Número de Serie')
                            ->numeric(),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(4)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('keywords')
                            ->label('Palabras Clave')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Categorías y Derechos')
                    ->schema([
                        Forms\Components\TextInput::make('categories')
                            ->label('Categorías')
                            ->helperText('JSON array format')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('age_range')
                            ->label('Rango de Edad')
                            ->maxLength(50),

                        Forms\Components\Textarea::make('rights')
                            ->label('Derechos')
                            ->rows(2),

                        Forms\Components\Textarea::make('ai_declaration')
                            ->label('Declaración IA')
                            ->rows(2),
                    ])
                    ->columns(2),
            ]);
    }
}
