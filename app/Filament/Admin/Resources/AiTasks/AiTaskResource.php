<?php

namespace App\Filament\Admin\Resources\AiTasks;

use App\Filament\Admin\Resources\AiTasks\Tables\AiTasksTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\AiTask;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class AiTaskResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'work';

    protected static ?string $slug = 'ai-tasks';

    protected static ?string $model = AiTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationLabel = 'Tareas IA';

    protected static ?string $pluralLabel = 'Tareas IA';

    protected static ?string $recordTitleAttribute = 'task_type';

    protected static ?string $navigationGroup = 'Inteligencia artificial';

    public static function form(Form $form): Form
    {
        return Schemas\AiTaskForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return AiTasksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAiTasks::route('/'),
            'create' => Pages\CreateAiTask::route('/create'),
            'edit' => Pages\EditAiTask::route('/{record}/edit'),
        ];
    }
}
