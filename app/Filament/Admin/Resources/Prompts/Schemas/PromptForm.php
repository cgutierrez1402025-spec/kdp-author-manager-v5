<?php

namespace App\Filament\Admin\Resources\Prompts\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class PromptForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Prompt')
                    ->schema([
                        Forms\Components\Select::make('work_id')
                            ->relationship('work', 'title_public', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->where('user_id', auth()->id())
                            )
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('task_id', null))
                            ->label('Obra')
                            ->required(),

                        Forms\Components\Select::make('ai_tool_id')
                            ->relationship('aiTool', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->where('user_id', auth()->id())
                            )
                            ->label('Herramienta IA')
                            ->required(),

                        Forms\Components\Select::make('task_id')
                            ->relationship('task', 'task_type', modifyQueryUsing: fn ($query, $get) => $query->when($get('work_id'), fn ($q, $workId) => $q->where('work_id', $workId))
                            )
                            ->label('Tarea IA')
                            ->nullable(),

                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('language_code')
                            ->label('Idioma')
                            ->options([
                                'es' => 'Español',
                                'en' => 'Inglés',
                            ])
                            ->default('es'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contenido')
                    ->schema([
                        Forms\Components\Textarea::make('prompt_text')
                            ->label('Prompt')
                            ->required()
                            ->rows(6),

                        Forms\Components\Textarea::make('result_text')
                            ->label('Resultado')
                            ->rows(6)
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Metadatos')
                    ->schema([
                        Forms\Components\TextInput::make('purpose')
                            ->label('Propósito')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('result_summary')
                            ->label('Resumen')
                            ->rows(2),

                        Forms\Components\Toggle::make('reused')
                            ->label('Reutilizado'),

                        Forms\Components\Toggle::make('generated_final_content')
                            ->label('Contenido Final'),

                        Forms\Components\Select::make('rating')
                            ->label('Rating')
                            ->options(array_combine(range(1, 5), range(1, 5))),
                    ])
                    ->columns(2),
            ]);
    }
}
