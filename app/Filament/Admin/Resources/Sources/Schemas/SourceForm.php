<?php

namespace App\Filament\Admin\Resources\Sources\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class SourceForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información de Fuente')
                    ->schema([
                        Forms\Components\Select::make('work_id')
                            ->relationship('work', 'title_public', modifyQueryUsing: fn ($query) => auth()->user()?->canViewAllAuthorData() ? $query : $query->where('user_id', auth()->id())
                            )
                            ->searchable()
                            ->preload()
                            ->label('Obra')
                            ->required(),

                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(512),

                        Forms\Components\TextInput::make('author')
                            ->label('Autor')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('year')
                            ->label('Año')
                            ->maxLength(20),

                        Forms\Components\Select::make('source_type')
                            ->label('Tipo')
                            ->options([
                                'book' => 'Libro',
                                'article' => 'Artículo',
                                'website' => 'Sitio Web',
                                'interview' => 'Entrevista',
                                'other' => 'Otro',
                            ])
                            ->required(),

                        Forms\Components\Select::make('language_code')
                            ->label('Idioma')
                            ->options([
                                'es' => 'Español',
                                'en' => 'Inglés',
                                'fr' => 'Francés',
                                'de' => 'Alemán',
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->maxLength(512),

                        Forms\Components\Textarea::make('citation')
                            ->label('Cita')
                            ->rows(3),

                        Forms\Components\Textarea::make('summary')
                            ->label('Resumen')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Archivo')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Archivo')
                            ->directory('sources')
                            ->visibility('private')
                            ->maxSize(10240),
                    ]),

                Forms\Components\Section::make('Metadatos Legales')
                    ->schema([
                        Forms\Components\TextInput::make('rights_status')
                            ->label('Derechos')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('license')
                            ->label('Licencia')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('reliability')
                            ->label('Fiabilidad')
                            ->maxLength(50),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ]),
            ]);
    }
}
