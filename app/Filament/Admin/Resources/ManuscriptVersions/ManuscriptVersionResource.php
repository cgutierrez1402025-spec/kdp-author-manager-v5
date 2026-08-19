<?php

namespace App\Filament\Admin\Resources\ManuscriptVersions;

use App\Filament\Admin\Resources\ManuscriptVersions\Pages\CreateManuscriptVersion;
use App\Filament\Admin\Resources\ManuscriptVersions\Pages\EditManuscriptVersion;
use App\Filament\Admin\Resources\ManuscriptVersions\Pages\ListManuscriptVersions;
use App\Filament\Admin\Resources\ManuscriptVersions\Pages\ViewManuscriptVersion;
use App\Filament\Admin\Resources\ManuscriptVersions\Schemas\ManuscriptVersionForm;
use App\Filament\Admin\Resources\ManuscriptVersions\Tables\ManuscriptVersionsTable;
use App\Filament\Admin\Resources\ManuscriptVersions\Widgets\VersionTreeWidget;
use App\Models\ManuscriptVersion;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManuscriptVersionResource extends Resource
{
    protected static ?string $slug = 'manuscript-versions';

    protected static ?string $model = ManuscriptVersion::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?string $navigationLabel = 'Versiones de Manuscrito';

    protected static ?string $navigationGroup = 'Catálogo editorial';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Versión';

    protected static ?string $pluralModelLabel = 'Versiones';

    protected static ?string $recordTitleAttribute = 'version_number';

    public static function form(Form $form): Form
    {
        return ManuscriptVersionForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return ManuscriptVersionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return auth()->user()?->canViewAllAuthorData()
            ? $query
            : $query->whereHas('work', fn (Builder $workQuery) => $workQuery->where('user_id', auth()->id()));
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ChaptersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListManuscriptVersions::route('/'),
            'create' => CreateManuscriptVersion::route('/create'),
            'edit' => EditManuscriptVersion::route('/{record}/edit'),
            'view' => ViewManuscriptVersion::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            VersionTreeWidget::class,
        ];
    }
}
