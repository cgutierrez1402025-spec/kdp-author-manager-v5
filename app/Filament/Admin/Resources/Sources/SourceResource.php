<?php

namespace App\Filament\Admin\Resources\Sources;

use App\Filament\Admin\Resources\Sources\Pages\CreateSource;
use App\Filament\Admin\Resources\Sources\Pages\EditSource;
use App\Filament\Admin\Resources\Sources\Pages\ListSources;
use App\Filament\Admin\Resources\Sources\Schemas\SourceForm;
use App\Filament\Admin\Resources\Sources\Tables\SourcesTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\Source;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class SourceResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'work';

    protected static ?string $slug = 'sources';

    protected static ?string $navigationLabel = 'Fuentes';

    protected static ?string $navigationGroup = 'Documentación';

    protected static ?string $model = Source::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return SourceForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return SourcesTable::configure($table);
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
            'index' => ListSources::route('/'),
            'create' => CreateSource::route('/create'),
            'edit' => EditSource::route('/{record}/edit'),
        ];
    }
}
