<?php

namespace App\Filament\Admin\Resources\PromotionCosts;

use App\Filament\Admin\Resources\PromotionCosts\Pages\CreatePromotionCost;
use App\Filament\Admin\Resources\PromotionCosts\Pages\EditPromotionCost;
use App\Filament\Admin\Resources\PromotionCosts\Pages\ListPromotionCosts;
use App\Filament\Admin\Resources\PromotionCosts\Schemas\PromotionCostForm;
use App\Filament\Admin\Resources\PromotionCosts\Tables\PromotionCostsTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\PromotionCost;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PromotionCostResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'bookPromotion.publication.work';

    protected static ?string $slug = 'promotion-costs';

    protected static ?string $model = PromotionCost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Costos de Promoción';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $recordTitleAttribute = 'cost_type';

    public static function form(Form $form): Form
    {
        return PromotionCostForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PromotionCostsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotionCosts::route('/'),
            'create' => CreatePromotionCost::route('/create'),
            'edit' => EditPromotionCost::route('/{record}/edit'),
        ];
    }
}
