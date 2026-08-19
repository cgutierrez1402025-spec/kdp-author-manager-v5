<?php

namespace App\Filament\Admin\Resources\Checklists\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class ChecklistForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lista de Verificación')
                    ->schema([
                        Forms\Components\Select::make('work_id')
                            ->relationship('work', 'title_public', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->where('user_id', auth()->id())
                            )
                            ->label('Obra')
                            ->required(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2),
                    ]),
            ]);
    }
}
