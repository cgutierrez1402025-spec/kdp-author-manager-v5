<?php

namespace App\Filament\Admin\Resources\Works\Schemas;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class WorkForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
                    ->schema([
                        Forms\Components\Select::make('series_id')
                            ->relationship(
                                'series',
                                'title',
                                modifyQueryUsing: fn ($query) => auth()->user()?->canViewAllAuthorData()
                                    ? $query
                                    : $query->where('user_id', auth()->id()),
                            )
                            ->label('Serie')
                            ->nullable(),

                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->minLength(3)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->helperText('Se genera automáticamente y puede ajustarse antes de guardar.')
                            ->unique(ignoreRecord: true)
                            ->minLength(3)
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('series_number')
                            ->label('Número en Serie')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\TextInput::make('title_internal')
                            ->label('Título Interno')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('title_public')
                            ->label('Título Público')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtítulo')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('author_name')
                            ->label('Nombre del Autor')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('pen_name')
                            ->label('Seudónimo')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles de la Obra')
                    ->schema([
                        Forms\Components\TextInput::make('genre')
                            ->label('Género')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('subgenre')
                            ->label('Subgénero')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('work_type')
                            ->label('Tipo de Obra')
                            ->maxLength(100),

                        Forms\Components\Select::make('original_language')
                            ->label('Idioma Original')
                            ->options([
                                'es' => 'Español',
                                'en' => 'Inglés',
                                'ca' => 'Valenciano / Catalán',
                                'fr' => 'Francés',
                                'de' => 'Alemán',
                                'it' => 'Italiano',
                                'pt' => 'Portugués',
                            ])
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'idea' => 'Idea',
                                'redaccion' => 'Redacción',
                                'revision' => 'Revisión',
                                'preparacion' => 'Preparación',
                                'publicada' => 'Publicada',
                            ])
                            ->default('idea')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Descripción y Notas')
                    ->description('Contenido comercial visible y notas internas del equipo.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Textarea::make('description_marketing')
                            ->label('Descripción de Marketing')
                            ->helperText('Texto orientado a la ficha comercial y campañas.')
                            ->rows(5),

                        Forms\Components\Textarea::make('description_internal')
                            ->label('Descripción Interna')
                            ->rows(4),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ]),

                Forms\Components\Section::make('Fechas y Público')
                    ->collapsible()
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Fecha de Inicio'),

                        Forms\Components\DatePicker::make('planned_publish_date')
                            ->label('Fecha Prevista de Publicación'),

                        Forms\Components\TextInput::make('target_audience')
                            ->label('Público Objetivo')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('age_recommendation')
                            ->label('Recomendación de Edad')
                            ->maxLength(50),
                    ])
                    ->columns(2),
            ]);
    }
}
