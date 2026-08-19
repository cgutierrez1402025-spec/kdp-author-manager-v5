<?php

namespace App\Filament\Admin\Resources\KdpSelectPeriods;

use App\Filament\Admin\Resources\KdpSelectPeriods\Pages\CreateKdpSelectPeriod;
use App\Filament\Admin\Resources\KdpSelectPeriods\Pages\EditKdpSelectPeriod;
use App\Filament\Admin\Resources\KdpSelectPeriods\Pages\ListKdpSelectPeriods;
use App\Filament\Admin\Resources\KdpSelectPeriods\Schemas\KdpSelectPeriodForm;
use App\Filament\Admin\Resources\KdpSelectPeriods\Tables\KdpSelectPeriodsTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\KdpSelectPeriod;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class KdpSelectPeriodResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'publication.work';

    protected static ?string $slug = 'kdp-select-periods';

    protected static ?string $model = KdpSelectPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Períodos KDP Select';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return KdpSelectPeriodForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return KdpSelectPeriodsTable::configure($table);
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
            'index' => ListKdpSelectPeriods::route('/'),
            'create' => CreateKdpSelectPeriod::route('/create'),
            'edit' => EditKdpSelectPeriod::route('/{record}/edit'),
        ];
    }
}
