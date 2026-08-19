<?php

namespace App\Filament\Admin\Resources\Prompts;

use App\Filament\Admin\Resources\Prompts\Pages\CreatePrompt;
use App\Filament\Admin\Resources\Prompts\Pages\EditPrompt;
use App\Filament\Admin\Resources\Prompts\Pages\ListPrompts;
use App\Filament\Admin\Resources\Prompts\Schemas\PromptForm;
use App\Filament\Admin\Resources\Prompts\Tables\PromptsTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\Prompt;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PromptResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'work';

    protected static ?string $slug = 'prompts';

    protected static ?string $model = Prompt::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Prompts';

    protected static ?string $navigationGroup = 'Inteligencia artificial';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return PromptForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PromptsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrompts::route('/'),
            'create' => CreatePrompt::route('/create'),
            'edit' => EditPrompt::route('/{record}/edit'),
        ];
    }
}
