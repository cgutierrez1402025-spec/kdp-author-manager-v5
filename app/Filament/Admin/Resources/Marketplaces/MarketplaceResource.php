<?php

namespace App\Filament\Admin\Resources\Marketplaces;

use App\Filament\Admin\Resources\Marketplaces\Pages\CreateMarketplace;
use App\Filament\Admin\Resources\Marketplaces\Pages\EditMarketplace;
use App\Filament\Admin\Resources\Marketplaces\Pages\ListMarketplaces;
use App\Filament\Admin\Resources\Marketplaces\Schemas\MarketplaceForm;
use App\Filament\Admin\Resources\Marketplaces\Tables\MarketplacesTable;
use App\Models\Marketplace;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class MarketplaceResource extends Resource
{
    protected static ?string $slug = 'marketplaces';

    protected static ?string $navigationLabel = 'Mercados';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $model = Marketplace::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return MarketplaceForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return MarketplacesTable::configure($table);
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
            'index' => ListMarketplaces::route('/'),
            'create' => CreateMarketplace::route('/create'),
            'edit' => EditMarketplace::route('/{record}/edit'),
        ];
    }
}
