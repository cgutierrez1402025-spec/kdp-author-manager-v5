<?php

namespace App\Filament\Admin\Resources\Tasks\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class TaskForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles de Tarea')
                    ->schema([
                        Forms\Components\Select::make('work_id')
                            ->relationship('work', 'title_public', modifyQueryUsing: fn ($query) => auth()->user()?->canViewAllAuthorData() ? $query : $query->where('user_id', auth()->id())
                            )
                            ->searchable()
                            ->preload()
                            ->label('Obra')
                            ->required(),

                        Forms\Components\Select::make('assigned_to')
                            ->relationship('assignedTo', 'name')
                            ->label('Asignado a')
                            ->nullable(),

                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('task_type')
                            ->label('Tipo')
                            ->options([
                                'writing' => 'Escritura',
                                'editing' => 'Edición',
                                'cover' => 'Portada',
                                'formatting' => 'Formato',
                                'publishing' => 'Publicación',
                                'marketing' => 'Marketing',
                            ]),

                        Forms\Components\Select::make('priority')
                            ->label('Prioridad')
                            ->options([
                                'low' => 'Baja',
                                'medium' => 'Media',
                                'high' => 'Alta',
                                'urgent' => 'Urgente',
                            ])
                            ->default('medium'),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'pending' => 'Pendiente',
                                'in_progress' => 'En Progreso',
                                'completed' => 'Completada',
                                'cancelled' => 'Cancelada',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Fecha Límite')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
