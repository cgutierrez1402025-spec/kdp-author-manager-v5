<?php

namespace App\Filament\Admin\Resources\Checklists;

use App\Filament\Admin\Resources\Checklists\Pages\CreateChecklist;
use App\Filament\Admin\Resources\Checklists\Pages\EditChecklist;
use App\Filament\Admin\Resources\Checklists\Pages\ListChecklists;
use App\Filament\Admin\Resources\Checklists\RelationManagers\ItemsRelationManager;
use App\Filament\Admin\Resources\Checklists\Schemas\ChecklistForm;
use App\Filament\Admin\Resources\Checklists\Tables\ChecklistsTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\Checklist;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ChecklistResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'work';

    protected static ?string $slug = 'checklists';

    protected static ?string $model = Checklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Listas de Verificación';

    protected static ?string $navigationGroup = 'Catálogo editorial';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return ChecklistForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return ChecklistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ItemsRelationManager::class,  // ✅ Corregido: import completo
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChecklists::route('/'),
            'create' => CreateChecklist::route('/create'),
            'edit' => EditChecklist::route('/{record}/edit'),
        ];
    }
}
