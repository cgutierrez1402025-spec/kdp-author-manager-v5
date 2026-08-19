<?php

namespace App\Filament\Admin\Resources\KdpReportRows;

use App\Filament\Admin\Resources\KdpReportRows\Pages\ListKdpReportRows;
use App\Filament\Admin\Resources\Publications\PublicationResource;
use App\Models\KdpReportRow;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KdpReportRowResource extends Resource
{
    protected static ?string $model = KdpReportRow::class;

    protected static ?string $slug = 'desglose-informes-kdp';

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationLabel = 'Desglose de informes';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $pluralModelLabel = 'desglose de informes KDP';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('has_non_zero')->label('Con datos')->boolean()->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable()->wrap()->width('24rem'),
                Tables\Columns\TextColumn::make('author')->label('Autor')->searchable()->wrap()->toggleable(),
                Tables\Columns\TextColumn::make('asin')->label('ASIN')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('report_type')->label('Informe')->badge(),
                Tables\Columns\TextColumn::make('row_kind')->label('Tipo de dato')->badge(),
                Tables\Columns\TextColumn::make('transaction_date')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('report_period')->label('Periodo')->date('m/Y')->sortable(),
                Tables\Columns\TextColumn::make('net_units_sold')->label('Unidades netas')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('kenp_read')->label('KENP')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('total_earnings')->label('Regalías')->numeric(2)->sortable(),
                Tables\Columns\TextColumn::make('currency')->label('Moneda'),
                Tables\Columns\TextColumn::make('marketplace')->label('Marketplace')->toggleable(),
                Tables\Columns\TextColumn::make('importBatch.original_file_name')->label('Archivo')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('with_data')
                    ->label('Sólo filas con valores distintos de cero')
                    ->query(fn (Builder $query): Builder => $query->withNonZeroValues()),
                Tables\Filters\SelectFilter::make('report_type')->label('Informe')->options([
                    'dashboard' => 'Panel', 'orders' => 'Pedidos', 'kenp' => 'KENP',
                    'preorders' => 'Preventas', 'prior_royalties' => 'Regalías anteriores',
                    'royalties_estimator' => 'Estimador', 'payments' => 'Pagos',
                    'sales_royalties' => 'Ventas y regalías',
                ]),
                Tables\Filters\SelectFilter::make('row_kind')->label('Tipo de dato')->options([
                    'royalty' => 'Regalía', 'royalty_estimate' => 'Estimación', 'order' => 'Pedido',
                    'preorder' => 'Preventa', 'kenp' => 'KENP', 'payment' => 'Pago',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('publication')
                    ->label('Ver publicación')->icon('heroicon-o-arrow-top-right-on-square')
                    ->visible(fn (KdpReportRow $record): bool => (bool) $record->publication_id)
                    ->url(fn (KdpReportRow $record): string => PublicationResource::getUrl('edit', ['record' => $record->publication_id])),
            ])
            ->paginationPageOptions([25, 50, 100, 'all'])
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading('No hay filas importadas de KDP');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select('kdp_report_rows.*')
            ->selectRaw('CASE WHEN '.KdpReportRow::nonZeroSql('kdp_report_rows').' THEN 1 ELSE 0 END AS has_non_zero')
            ->orderByDataPresence()
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return ['index' => ListKdpReportRows::route('/')];
    }
}
