<?php

namespace App\Filament\Admin\Resources\ManuscriptVersions\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class ManuscriptVersionForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Básica')
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
                            ->afterStateUpdated(function ($set): void {
                                $set('work_language_id', null);
                                $set('parent_version_id', null);
                                $set('edition_id', null);
                            }),

                        Forms\Components\Select::make('work_language_id')
                            ->relationship(
                                'workLanguage',
                                'language_code',
                                modifyQueryUsing: fn ($query, $get) => $query->where('work_id', $get('work_id')),
                            )
                            ->label('Idioma')
                            ->required()
                            ->live(),

                        Forms\Components\Select::make('parent_version_id')
                            ->relationship(
                                'parentVersion',
                                'version_number',
                                modifyQueryUsing: fn ($query, $get) => $query
                                    ->where('work_id', $get('work_id'))
                                    ->where('work_language_id', $get('work_language_id')),
                            )
                            ->label('Versión Padre')
                            ->nullable(),

                        Forms\Components\Select::make('edition_id')
                            ->relationship('edition', 'edition_number', modifyQueryUsing: fn ($query, $get) => $query
                                ->where('work_id', $get('work_id'))
                                ->where('work_language_id', $get('work_language_id'))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->edition_name ?? "Edición {$record->edition_number}")
                            ->label('Edición')
                            ->nullable(),

                        Forms\Components\TextInput::make('version_number')
                            ->label('Número de Versión')
                            ->required()
                            ->maxLength(50),

                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->maxLength(255),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'draft' => 'Borrador',
                                'review' => 'Revisión',
                                'final' => 'Final',
                                'published' => 'Publicado',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Contenido')
                    ->schema([
                        Forms\Components\RichEditor::make('html_content')
                            ->label('Contenido HTML')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('file_path')
                            ->label('Archivo')
                            ->maxLength(512),
                    ]),

                Forms\Components\Section::make('Opciones')
                    ->schema([
                        Forms\Components\Toggle::make('is_candidate')
                            ->label('Candidata'),

                        Forms\Components\Toggle::make('is_final')
                            ->label('Final'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Publicada'),

                        Forms\Components\Textarea::make('change_summary')
                            ->label('Resumen de Cambios')
                            ->rows(2),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Estadísticas')
                    ->schema([
                        Forms\Components\TextInput::make('word_count')
                            ->label('Palabras')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('chapter_count')
                            ->label('Capítulos')
                            ->numeric()
                            ->disabled(),

                        Forms\Components\TextInput::make('image_count')
                            ->label('Imágenes')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(3),
            ]);
    }
}
