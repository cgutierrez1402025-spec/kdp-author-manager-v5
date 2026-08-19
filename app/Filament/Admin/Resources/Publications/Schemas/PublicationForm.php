<?php

namespace App\Filament\Admin\Resources\Publications\Schemas;

use Filament\Forms;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;

class PublicationForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('work_selection')
                        ->label('Seleccionar Obra')
                        ->schema([
                            Forms\Components\Select::make('work_id')
                                ->relationship(
                                    'work',
                                    'title_public',
                                    modifyQueryUsing: fn ($query) => auth()->user()?->canViewAllAuthorData()
                                        ? $query
                                        : $query->where('user_id', auth()->id()),
                                )
                                ->label('Obra')
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state, $set) => $set('work_language_id', null)),
                        ]),

                    Wizard\Step::make('language_selection')
                        ->label('Seleccionar Idioma')
                        ->schema([
                            Forms\Components\Select::make('work_language_id')
                                ->relationship('workLanguage', 'language_code', modifyQueryUsing: function ($query, $get) {
                                    return $query->where('work_id', $get('work_id'));
                                })
                                ->label('Idioma')
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state, $set) => $set('manuscript_version_id', null)),
                        ]),

                    Wizard\Step::make('manuscript_selection')
                        ->label('Seleccionar Manuscrito')
                        ->schema([
                            Forms\Components\Select::make('manuscript_version_id')
                                ->relationship('manuscriptVersion', 'name', modifyQueryUsing: function ($query, $get) {
                                    $workId = $get('work_id');
                                    $workLanguageId = $get('work_language_id');

                                    return $query->where('work_id', $workId)
                                        ->where('work_language_id', $workLanguageId)
                                        ->where('is_final', true);
                                })
                                ->label('Manuscrito Final')
                                ->required(),
                        ]),

                    Wizard\Step::make('platform_configuration')
                        ->label('Configurar Plataforma')
                        ->schema([
                            Forms\Components\Select::make('platform_id')
                                ->relationship('platform', 'name')
                                ->label('Plataforma')
                                ->required()
                                ->live()
                                ->afterStateUpdated(fn ($state, $set) => $set('marketplace_id', null)),

                            Forms\Components\Select::make('marketplace_id')
                                ->relationship('marketplace', 'name', modifyQueryUsing: function ($query, $get) {
                                    return $query->where('platform_id', $get('platform_id'));
                                })
                                ->label('Marketplace')
                                ->required(),

                            Forms\Components\Select::make('format')
                                ->label('Formato')
                                ->options([
                                    'ebook' => 'eBook',
                                    'paperback' => 'Tapa Blanda',
                                    'hardcover' => 'Tapa Dura',
                                    'audiobook' => 'Audiolibro',
                                ])
                                ->required(),

                            Forms\Components\Select::make('status')
                                ->label('Estado')
                                ->options([
                                    'draft' => 'Borrador',
                                    'processing' => 'En proceso',
                                    'published' => 'Publicada',
                                    'error' => 'Con errores',
                                ])
                                ->default('draft')
                                ->required(),

                            Forms\Components\TextInput::make('price')
                                ->label('Precio')
                                ->numeric()
                                ->step('0.01')
                                ->nullable(),

                            Forms\Components\TextInput::make('currency')
                                ->label('Moneda')
                                ->maxLength(3)
                                ->default('USD'),

                            Forms\Components\TextInput::make('isbn')
                                ->label('ISBN')
                                ->maxLength(20),

                            Forms\Components\TextInput::make('asin')
                                ->label('ASIN')
                                ->maxLength(20),
                        ])
                        ->columns(2),

                    Wizard\Step::make('kdp_metadata')
                        ->label('Metadatos KDP')
                        ->schema([
                            Forms\Components\TextInput::make('kdpMetadata.title')
                                ->label('Título KDP')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('kdpMetadata.subtitle')
                                ->label('Subtítulo KDP')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('kdpMetadata.author')
                                ->label('Autor')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('kdpMetadata.series_name')
                                ->label('Nombre de Serie')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('kdpMetadata.series_number')
                                ->label('Número de Serie')
                                ->numeric(),

                            Forms\Components\Textarea::make('kdpMetadata.description')
                                ->label('Descripción')
                                ->rows(4),

                            Forms\Components\TextInput::make('kdpMetadata.keywords')
                                ->label('Palabras Clave')
                                ->maxLength(255),

                            Forms\Components\TextInput::make('kdpMetadata.age_range')
                                ->label('Rango de Edad')
                                ->maxLength(50),

                            Forms\Components\Textarea::make('kdpMetadata.ai_declaration')
                                ->label('Declaración IA')
                                ->rows(3),
                        ]),
                ]),
            ]);
    }
}
