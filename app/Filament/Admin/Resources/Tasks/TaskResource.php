<?php

namespace App\Filament\Admin\Resources\Tasks;

use App\Filament\Admin\Resources\Tasks\Pages\CreateTask;
use App\Filament\Admin\Resources\Tasks\Pages\EditTask;
use App\Filament\Admin\Resources\Tasks\Pages\ListTasks;
use App\Filament\Admin\Resources\Tasks\Schemas\TaskForm;
use App\Filament\Admin\Resources\Tasks\Tables\TasksTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\Task;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class TaskResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'work';

    protected static ?string $slug = 'tasks';

    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Tareas';

    protected static ?string $navigationGroup = 'Catálogo editorial';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->overdue()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tareas vencidas';
    }

    public static function form(Form $form): Form
    {
        return TaskForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return TasksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTasks::route('/'),
            'create' => CreateTask::route('/create'),
            'edit' => EditTask::route('/{record}/edit'),
        ];
    }
}
