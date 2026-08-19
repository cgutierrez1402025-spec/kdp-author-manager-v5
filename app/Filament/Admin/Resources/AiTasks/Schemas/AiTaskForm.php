<?php

namespace App\Filament\Admin\Resources\AiTasks\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class AiTaskForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tarea de IA')
                    ->schema([
                        Forms\Components\Select::make('work_id')
                            ->relationship('work', 'title_public', modifyQueryUsing: fn ($query) => auth()->user()?->canViewAllAuthorData() ? $query : $query->where('user_id', auth()->id())
                            )
                            ->label('Obra')
                            ->required(),

                        Forms\Components\Select::make('preferred_ai_tool_id')
                            ->relationship('preferredAiTool', 'name', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->where('user_id', auth()->id())
                            )
                            ->label('Herramienta IA Preferida')
                            ->nullable(),

                        Forms\Components\TextInput::make('task_type')
                            ->label('Tipo de Tarea')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }
}
