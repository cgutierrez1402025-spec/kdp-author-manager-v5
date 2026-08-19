<?php

namespace App\Filament\Admin\Resources\SourceUsages;

use App\Filament\Admin\Resources\SourceUsages\Pages\CreateSourceUsage;
use App\Filament\Admin\Resources\SourceUsages\Pages\EditSourceUsage;
use App\Filament\Admin\Resources\SourceUsages\Pages\ListSourceUsages;
use App\Filament\Admin\Resources\SourceUsages\Schemas\SourceUsageForm;
use App\Filament\Admin\Resources\SourceUsages\Tables\SourceUsagesTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\SourceUsage;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class SourceUsageResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'work';

    protected static ?string $slug = 'source-usages';

    protected static ?string $model = SourceUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Usos de Fuente';

    protected static ?string $navigationGroup = 'Documentación';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return SourceUsageForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return SourceUsagesTable::configure($table);
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
            'index' => ListSourceUsages::route('/'),
            'create' => CreateSourceUsage::route('/create'),
            'edit' => EditSourceUsage::route('/{record}/edit'),
        ];
    }
}
