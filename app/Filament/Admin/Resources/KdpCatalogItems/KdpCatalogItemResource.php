<?php

namespace App\Filament\Admin\Resources\KdpCatalogItems;

use App\Filament\Admin\Resources\KdpCatalogItems\Pages\ListKdpCatalogItems;
use App\Filament\Admin\Resources\Works\WorkResource;
use App\Models\KdpCatalogItem;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KdpCatalogItemResource extends Resource
{
    protected static ?string $model = KdpCatalogItem::class;

    protected static ?string $slug = 'catalogo-detectado-kdp';

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationLabel = 'Catálogo detectado KDP';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $pluralModelLabel = 'catálogo detectado KDP';

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('last_seen_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->limit(60)->tooltip(fn (KdpCatalogItem $record) => $record->title),
                Tables\Columns\TextColumn::make('author')->label('Autor')->searchable(),
                Tables\Columns\TextColumn::make('asin')->label('ASIN')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('isbn')->label('ISBN')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('format')->label('Formato')->badge(),
                Tables\Columns\TextColumn::make('marketplaces')->label('Marketplaces')->formatStateUsing(fn ($state) => implode(', ', is_array($state) ? $state : []))->wrap(),
                Tables\Columns\TextColumn::make('review_status')->label('Estado')->badge()->formatStateUsing(fn (string $state) => $state === 'linked' ? 'Vinculada' : 'Pendiente de revisión')->color(fn (string $state) => $state === 'linked' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('last_seen_at')->label('Última aparición')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('review_status')->label('Estado')->options([
                    'linked' => 'Vinculada',
                    'pending' => 'Pendiente de revisión',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('openWork')
                    ->label('Abrir obra')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->visible(fn (KdpCatalogItem $record) => $record->work_id !== null)
                    ->url(fn (KdpCatalogItem $record) => WorkResource::getUrl('view', ['record' => $record->work_id])),
            ])
            ->emptyStateHeading('No se han detectado obras en informes KDP')
            ->emptyStateDescription('Al importar un informe, cada título nuevo aparecerá aquí para vincularlo o revisarlo.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return ['index' => ListKdpCatalogItems::route('/')];
    }
}
