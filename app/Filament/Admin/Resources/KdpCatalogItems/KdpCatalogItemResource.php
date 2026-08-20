<?php

namespace App\Filament\Admin\Resources\KdpCatalogItems;

use App\Filament\Admin\Resources\KdpCatalogItems\Pages\ListKdpCatalogItems;
use App\Filament\Admin\Resources\Works\WorkResource;
use App\Models\KdpCatalogItem;
use App\Models\Language;
use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\Work;
use App\Models\WorkLanguage;
use App\Services\Kdp\KdpCatalogPromotionService;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
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
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->sortable()->limit(60)->tooltip(fn (KdpCatalogItem $record) => $record->title),
                Tables\Columns\TextColumn::make('author')->label('Autor')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('asin')->label('ASIN')->searchable()->sortable()->copyable(),
                Tables\Columns\TextColumn::make('isbn')->label('ISBN')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('format')->label('Formato')->badge()->sortable(),
                Tables\Columns\TextColumn::make('work.title_public')->label('Obra vinculada')->searchable()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('report_rows_count')->counts('reportRows')->label('Filas origen')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('marketplaces')->label('Marketplaces')->formatStateUsing(fn ($state) => implode(', ', is_array($state) ? $state : []))->wrap(),
                Tables\Columns\TextColumn::make('review_status')->label('Estado')->badge()->sortable()->formatStateUsing(fn (string $state) => match ($state) {
                    'linked' => 'Vinculada','ignored' => 'Ignorada','ambiguous' => 'Ambigua','incomplete' => 'Incompleta',default => 'Pendiente de revisión'
                })->color(fn (string $state) => match ($state) {
                    'linked' => 'success','ignored' => 'gray','ambiguous' => 'danger',default => 'warning'
                }),
                Tables\Columns\TextColumn::make('last_seen_at')->label('Última aparición')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('review_status')->label('Estado')->options([
                    'linked' => 'Vinculada',
                    'pending' => 'Pendiente de revisión',
                    'ignored' => 'Ignorada',
                    'ambiguous' => 'Ambigua',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('createWork')
                    ->label('Crear obra y edición')->icon('heroicon-o-plus-circle')->color('success')
                    ->visible(fn (KdpCatalogItem $record) => $record->work_id === null && $record->review_status !== 'ignored')
                    ->fillForm(fn (KdpCatalogItem $record) => ['title' => $record->title, 'author_name' => $record->author, 'format' => $record->format])
                    ->form(self::promotionForm(includeWork: false))
                    ->action(function (KdpCatalogItem $record, array $data): void {
                        $work = app(KdpCatalogPromotionService::class)->createWork($record, $data, auth()->user());
                        Notification::make()->success()->title('Obra y edición creadas')->body("{$work->title_public} ya aparece en la tabla de obras.")->send();
                    }),
                Tables\Actions\Action::make('linkWork')
                    ->label('Vincular a obra')->icon('heroicon-o-link')
                    ->visible(fn (KdpCatalogItem $record) => $record->work_id === null && $record->review_status !== 'ignored')
                    ->form(self::promotionForm(includeWork: true))
                    ->action(function (KdpCatalogItem $record, array $data): void {
                        app(KdpCatalogPromotionService::class)->linkExisting($record, $data, auth()->user());
                        Notification::make()->success()->title('Catálogo vinculado')->send();
                    }),
                Tables\Actions\Action::make('ignore')->label('Ignorar')->icon('heroicon-o-eye-slash')->color('gray')
                    ->visible(fn (KdpCatalogItem $record) => $record->work_id === null && $record->review_status !== 'ignored')
                    ->requiresConfirmation()->action(fn (KdpCatalogItem $record) => $record->update(['review_status' => 'ignored'])),
                Tables\Actions\Action::make('openWork')
                    ->label('Abrir obra')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->visible(fn (KdpCatalogItem $record) => $record->work_id !== null)
                    ->url(fn (KdpCatalogItem $record) => WorkResource::getUrl('view', ['record' => $record->work_id])),
            ])
            ->emptyStateHeading('No se han detectado obras en informes KDP')
            ->emptyStateDescription('Al importar un informe, cada título nuevo aparecerá aquí para vincularlo o revisarlo.');
    }

    private static function promotionForm(bool $includeWork): array
    {
        $fields = [];
        if ($includeWork) {
            $fields[] = Forms\Components\Select::make('work_id')->label('Obra')->options(fn (KdpCatalogItem $record) => Work::where('user_id', $record->user_id)->pluck('title_public', 'id'))->searchable()->live()->required();
            $fields[] = Forms\Components\Select::make('work_language_id')->label('Idioma de la obra')->options(fn (Get $get) => WorkLanguage::where('work_id', $get('work_id'))->pluck('language_code', 'id'))->live()->required();
            $fields[] = Forms\Components\Select::make('manuscript_version_id')->label('Manuscrito final (opcional)')->options(fn (Get $get) => ManuscriptVersion::where('work_id', $get('work_id'))->where('work_language_id', $get('work_language_id'))->where('is_final', true)->pluck('name', 'id'));
        } else {
            $fields[] = Forms\Components\TextInput::make('title')->label('Título')->required();
            $fields[] = Forms\Components\TextInput::make('author_name')->label('Autor')->required();
            $fields[] = Forms\Components\Select::make('language_code')->label('Idioma original')->options(fn () => Language::where('active', true)->pluck('name', 'code'))->searchable()->required();
            $fields[] = Forms\Components\Select::make('work_type')->label('Tipo de obra')->options(['novel' => 'Novela', 'essay' => 'Ensayo', 'manual' => 'Manual', 'poetry' => 'Poesía', 'children' => 'Infantil', 'other' => 'Otro']);
        }
        $fields[] = Forms\Components\Select::make('marketplace_id')->label('Marketplace principal')->options(fn () => Marketplace::where('platform_id', Platform::where('name', 'Amazon KDP')->value('id'))->pluck('name', 'id'))->searchable()->required();
        $fields[] = Forms\Components\Select::make('format')->label('Formato')->options(['ebook' => 'eBook', 'paperback' => 'Tapa blanda', 'hardcover' => 'Tapa dura', 'audiobook' => 'Audiolibro'])->required();

        return $fields;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['work', 'publication'])
            ->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return ['index' => ListKdpCatalogItems::route('/')];
    }
}
