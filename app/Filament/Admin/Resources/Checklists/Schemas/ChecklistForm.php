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
                            ->relationship('work', 'title_public', modifyQueryUsing: fn ($query) => auth()->user()?->canViewAllAuthorData() ? $query : $query->where('user_id', auth()->id())
                            )
                            ->label('Obra')
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('publication_id', null))
                            ->required()
                            ->helperText('Una checklist puede aplicarse a toda la obra o a una publicación concreta.'),

                        Forms\Components\Select::make('publication_id')
                            ->relationship('publication', 'asin', modifyQueryUsing: fn ($query, $get) => $query->where('work_id', $get('work_id')))
                            ->label('Publicación (opcional)')
                            ->helperText('Déjalo vacío para una lista general de la obra.')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => trim(($record->asin ?: 'Sin ASIN').' · '.($record->format ?: 'Sin formato')))
                            ->searchable()
                            ->nullable(),

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
