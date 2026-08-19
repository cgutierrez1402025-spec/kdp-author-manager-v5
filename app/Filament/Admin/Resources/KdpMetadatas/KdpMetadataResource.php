<?php

namespace App\Filament\Admin\Resources\KdpMetadatas;

use App\Filament\Admin\Resources\KdpMetadatas\Pages\CreateKdpMetadata;
use App\Filament\Admin\Resources\KdpMetadatas\Pages\EditKdpMetadata;
use App\Filament\Admin\Resources\KdpMetadatas\Pages\ListKdpMetadatas;
use App\Filament\Admin\Resources\KdpMetadatas\Schemas\KdpMetadataForm;
use App\Filament\Admin\Resources\KdpMetadatas\Tables\KdpMetadatasTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\KdpMetadata;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class KdpMetadataResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'publication.work';

    protected static ?string $slug = 'kdp-metadata';

    protected static ?string $model = KdpMetadata::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Metadatos KDP';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return KdpMetadataForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return KdpMetadatasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKdpMetadatas::route('/'),
            'create' => CreateKdpMetadata::route('/create'),
            'edit' => EditKdpMetadata::route('/{record}/edit'),
        ];
    }
}
