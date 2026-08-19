<?php

namespace App\Filament\Admin\Resources\Platforms;

use App\Filament\Admin\Resources\Platforms\Pages\CreatePlatform;
use App\Filament\Admin\Resources\Platforms\Pages\EditPlatform;
use App\Filament\Admin\Resources\Platforms\Pages\ListPlatforms;
use App\Filament\Admin\Resources\Platforms\Schemas\PlatformForm;
use App\Filament\Admin\Resources\Platforms\Tables\PlatformsTable;
use App\Models\Platform;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PlatformResource extends Resource
{
    protected static ?string $slug = 'platforms';

    protected static ?string $model = Platform::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $navigationLabel = 'Plataformas';

    public static function form(Form $form): Form
    {
        return PlatformForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PlatformsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MarketplacesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlatforms::route('/'),
            'create' => CreatePlatform::route('/create'),
            'edit' => EditPlatform::route('/{record}/edit'),
        ];
    }
}
