<?php

namespace App\Filament\Admin\Resources\BookPromotions;

use App\Filament\Admin\Resources\BookPromotions\Pages\CreateBookPromotion;
use App\Filament\Admin\Resources\BookPromotions\Pages\EditBookPromotion;
use App\Filament\Admin\Resources\BookPromotions\Pages\ListBookPromotions;
use App\Filament\Admin\Resources\BookPromotions\Schemas\BookPromotionForm;
use App\Filament\Admin\Resources\BookPromotions\Tables\BookPromotionsTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\BookPromotion;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BookPromotionResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'publication.work';

    protected static ?string $slug = 'book-promotions';

    protected static ?string $model = BookPromotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Promociones';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $recordTitleAttribute = 'promotion_name';

    public static function form(Form $form): Form
    {
        return BookPromotionForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return BookPromotionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\DailyResultsRelationManager::class,
            RelationManagers\CostsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookPromotions::route('/'),
            'create' => CreateBookPromotion::route('/create'),
            'edit' => EditBookPromotion::route('/{record}/edit'),
        ];
    }
}
