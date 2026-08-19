<?php

namespace App\Filament\Admin\Resources\AiTools\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class AiToolForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Herramienta')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('provider')
                            ->label('Proveedor')
                            ->options([
                                'openai' => 'OpenAI',
                                'anthropic' => 'Anthropic',
                                'google' => 'Google',
                                'cohere' => 'Cohere',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('model')
                            ->label('Modelo')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->maxLength(512),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\Textarea::make('strengths')
                            ->label('Fortalezas')
                            ->rows(3),

                        Forms\Components\Textarea::make('weaknesses')
                            ->label('Debilidades')
                            ->rows(3),

                        Forms\Components\TextInput::make('quality_score')
                            ->label('Puntuación de Calidad')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),

                        Forms\Components\TextInput::make('cost_notes')
                            ->label('Notas de Coste')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ])
                    ->columns(2),
            ]);
    }
}
