<?php

namespace App\Filament\Admin\Resources\Tasks\Schemas;

use App\Models\TaskType;
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
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('publication_id', null))
                            ->required(),

                        Forms\Components\Select::make('publication_id')
                            ->relationship('publication', 'asin', modifyQueryUsing: fn ($query, $get) => $query->where('work_id', $get('work_id')))
                            ->label('Publicación (opcional)')
                            ->helperText('Déjalo vacío para una tarea general de la obra; selecciónalo para una edición concreta.')
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn ($record): string => trim(($record->asin ?: 'Sin ASIN').' · '.($record->format ?: 'Sin formato')))
                            ->nullable(),

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
                            ->label('Tipo heredado')
                            ->helperText('Se conserva para compatibilidad con tareas antiguas.')
                            ->options([
                                'writing' => 'Escritura',
                                'editing' => 'Edición',
                                'cover' => 'Portada',
                                'formatting' => 'Formato',
                                'publishing' => 'Publicación',
                                'marketing' => 'Marketing',
                            ]),

                        Forms\Components\Select::make('task_type_id')
                            ->relationship('taskType', 'name', modifyQueryUsing: fn ($query) => $query->where('is_active', true))
                            ->label('Tipo de tarea')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(100)->unique(TaskType::class, 'name'),
                                Forms\Components\TextInput::make('description')->label('Descripción'),
                            ])
                            ->createOptionUsing(fn (array $data): int => TaskType::create($data + ['is_active' => true])->getKey())
                            ->createOptionAction(fn ($action) => $action->label('Añadir tipo de tarea')->modalHeading('Nuevo tipo de tarea'))
                            ->searchable()
                            ->preload()
                            ->nullable(),

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
