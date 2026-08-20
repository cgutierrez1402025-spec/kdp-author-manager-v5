<?php

namespace App\Filament\Admin\Resources\Works\Schemas;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Language;
use App\Models\Genre;
use App\Models\Subgenre;
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
                            ->label('Identificador URL (slug)')
                            ->helperText('Texto corto y único que identifica la obra en una URL. Se genera automáticamente a partir del título.')
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
                            ->helperText('Nombre de trabajo usado dentro del equipo editorial; no tiene por qué ser público.')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('title_public')
                            ->label('Título Público')
                            ->helperText('Título que verá el lector en catálogos y publicaciones.')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('subtitle')
                            ->label('Subtítulo')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('author_name')
                            ->label('Nombre del Autor')
                            ->helperText('Nombre del autor que aparecerá en la ficha editorial.')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('pen_name')
                            ->label('Seudónimo')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detalles de la Obra')
                    ->schema([
                        Forms\Components\Select::make('genres')
                            ->label('Género principal')
                            ->helperText('Categoría editorial principal de la obra. KDP permite seleccionar hasta tres categorías por publicación.')
                            ->relationship('genres', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                            ->multiple()
                            ->maxItems(3)
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->label('Nombre del género')->required(),
                                Forms\Components\TextInput::make('slug')->label('Identificador')->required(),
                            ])
                            ->nullable(),

                        Forms\Components\Select::make('subgenres')
                            ->label('Subgénero')
                            ->helperText('Clasificación más concreta dentro del género principal.')
                            ->relationship('subgenres', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                            ->multiple()
                            ->maxItems(3)
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\Select::make('genre_id')->label('Género padre')->relationship('genre', 'name')->required(),
                                Forms\Components\TextInput::make('name')->label('Nombre del subgénero')->required(),
                                Forms\Components\TextInput::make('slug')->label('Identificador')->required(),
                            ])
                            ->nullable(),

                        Forms\Components\TextInput::make('work_type')
                            ->label('Tipo de Obra')
                            ->maxLength(100),

                        Forms\Components\Select::make('original_language')
                            ->label('Idioma Original')
                            ->options(fn () => Language::query()->where('is_active', true)->orderBy('name')->pluck('name', 'code'))
                            ->searchable()
                            ->helperText('Idioma principal en el que se creó la obra. Puedes añadir idiomas desde la tabla de idiomas.')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'catalog_review' => 'Pendiente de completar (KDP)',
                                'idea' => 'Idea',
                                'redaccion' => 'Redacción',
                                'revision' => 'Revisión',
                                'preparacion' => 'Preparación',
                                'publicada' => 'Publicada',
                            ])
                            ->helperText('Fase actual del trabajo editorial, desde la idea hasta su publicación.')
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
