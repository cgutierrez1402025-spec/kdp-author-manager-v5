<?php

namespace App\Filament\Admin\Resources\IllustrationAnchors;

use App\Filament\Admin\Resources\IllustrationAnchors\Pages\CreateIllustrationAnchor;
use App\Filament\Admin\Resources\IllustrationAnchors\Pages\EditIllustrationAnchor;
use App\Filament\Admin\Resources\IllustrationAnchors\Pages\ListIllustrationAnchors;
use App\Filament\Admin\Resources\IllustrationAnchors\Schemas\IllustrationAnchorForm;
use App\Filament\Admin\Resources\IllustrationAnchors\Tables\IllustrationAnchorsTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\IllustrationAnchor;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class IllustrationAnchorResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'manuscriptVersion.work';

    protected static ?string $slug = 'illustration-anchors';

    protected static ?string $model = IllustrationAnchor::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Anclajes de Ilustraciones';

    protected static ?string $navigationGroup = 'Ilustraciones';

    protected static ?string $recordTitleAttribute = 'anchor_type';

    public static function form(Form $form): Form
    {
        return IllustrationAnchorForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return IllustrationAnchorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIllustrationAnchors::route('/'),
            'create' => CreateIllustrationAnchor::route('/create'),
            'edit' => EditIllustrationAnchor::route('/{record}/edit'),
        ];
    }
}
