<?php

namespace App\Filament\Admin\Resources\Platforms\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class PlatformForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Plataforma')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3),
                    ]),
            ]);
    }
}
