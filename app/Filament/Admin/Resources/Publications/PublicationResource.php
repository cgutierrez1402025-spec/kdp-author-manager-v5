<?php

namespace App\Filament\Admin\Resources\Publications;

use App\Filament\Admin\Resources\Publications\Pages\CreatePublication;
use App\Filament\Admin\Resources\Publications\Pages\EditPublication;
use App\Filament\Admin\Resources\Publications\Pages\ListPublications;
use App\Filament\Admin\Resources\Publications\Schemas\PublicationForm;
use App\Filament\Admin\Resources\Publications\Tables\PublicationsTable;
use App\Models\Publication;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PublicationResource extends Resource
{
    protected static ?string $slug = 'publications';

    protected static ?string $model = Publication::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $recordTitleAttribute = 'format';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $navigationLabel = 'Ediciones publicadas';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->where('status', 'published')->count();
    }

    public static function form(Form $form): Form
    {
        return PublicationForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return PublicationsTable::configure($table);
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
            RelationManagers\KdpMetadataRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublications::route('/'),
            'create' => CreatePublication::route('/create'),
            'edit' => EditPublication::route('/{record}/edit'),
        ];
    }
}
