<?php

namespace App\Filament\Admin\Resources\PromotionDailyResults;

use App\Filament\Admin\Resources\PromotionDailyResults\Pages\CreatePromotionDailyResult;
use App\Filament\Admin\Resources\PromotionDailyResults\Pages\EditPromotionDailyResult;
use App\Filament\Admin\Resources\PromotionDailyResults\Pages\ListPromotionDailyResults;
use App\Filament\Admin\Resources\PromotionDailyResults\Schemas\PromotionDailyResultForm;
use App\Filament\Admin\Resources\PromotionDailyResults\Tables\PromotionDailyResultsTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\PromotionDailyResult;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class PromotionDailyResultResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'bookPromotion.publication.work';

    protected static ?string $slug = 'promotion-daily-results';

    protected static ?string $model = PromotionDailyResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Resultados Diarios';

    protected static ?string $navigationGroup = 'Marketing';

    protected static ?string $recordTitleAttribute = 'date';

    public static function form(Form $form): Form
    {
        return PromotionDailyResultForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PromotionDailyResultsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPromotionDailyResults::route('/'),
            'create' => CreatePromotionDailyResult::route('/create'),
            'edit' => EditPromotionDailyResult::route('/{record}/edit'),
        ];
    }
}
