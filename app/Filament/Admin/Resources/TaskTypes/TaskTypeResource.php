<?php

namespace App\Filament\Admin\Resources\TaskTypes;

use App\Filament\Admin\Resources\TaskTypes\Pages\CreateTaskType;
use App\Filament\Admin\Resources\TaskTypes\Pages\EditTaskType;
use App\Filament\Admin\Resources\TaskTypes\Pages\ListTaskTypes;
use App\Models\TaskType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TaskTypeResource extends Resource
{
    protected static ?string $model = TaskType::class;

    protected static ?string $slug = 'task-types';

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Tipos de tarea';

    protected static ?string $navigationGroup = 'Catálogo editorial';

    protected static ?string $modelLabel = 'tipo de tarea';

    protected static ?string $pluralModelLabel = 'tipos de tarea';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'author', 'editor']) ?? false;
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(100)
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('description')
                ->label('Descripción')
                ->maxLength(255),
            Forms\Components\Toggle::make('is_active')
                ->label('Activo')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Descripción')->wrap(),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
                Tables\Columns\TextColumn::make('tasks_count')->label('Tareas')->counts('tasks')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->headerActions([Tables\Actions\CreateAction::make()->label('Nuevo tipo')]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaskTypes::route('/'),
            'create' => CreateTaskType::route('/create'),
            'edit' => EditTaskType::route('/{record}/edit'),
        ];
    }
}
