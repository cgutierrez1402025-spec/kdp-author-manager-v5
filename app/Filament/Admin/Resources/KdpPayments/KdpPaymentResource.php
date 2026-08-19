<?php

namespace App\Filament\Admin\Resources\KdpPayments;

use App\Filament\Admin\Resources\KdpPayments\Pages\ListKdpPayments;
use App\Models\KdpPayment;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KdpPaymentResource extends Resource
{
    protected static ?string $model = KdpPayment::class;

    protected static ?string $slug = 'pagos-kdp';

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Pagos KDP';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $pluralModelLabel = 'pagos KDP';

    public static function table(Table $table): Table
    {
        return $table->defaultSort('payment_date', 'desc')->columns([
            Tables\Columns\TextColumn::make('payment_number')->label('Número')->searchable(),
            Tables\Columns\TextColumn::make('payment_date')->label('Fecha')->date('d/m/Y'),
            Tables\Columns\TextColumn::make('marketplace')->label('Marketplace'),
            Tables\Columns\TextColumn::make('payment_amount')->label('Importe')->money(fn ($record) => $record->currency),
            Tables\Columns\TextColumn::make('tax_withholding')->label('Retención')->numeric(2),
            Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
            Tables\Columns\TextColumn::make('allocations_count')->counts('allocations')->label('Asignaciones'),
            Tables\Columns\TextColumn::make('latestImportBatch.original_file_name')->label('Último informe'),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return ['index' => ListKdpPayments::route('/')];
    }
}
