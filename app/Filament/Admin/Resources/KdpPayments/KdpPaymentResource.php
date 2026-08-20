<?php

namespace App\Filament\Admin\Resources\KdpPayments;

use App\Filament\Admin\Resources\KdpPayments\Pages\ListKdpPayments;
use App\Models\KdpPayment;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('payment_number')->label('Número de pago')->helperText('Identificador del informe de pagos o referencia del distribuidor externo.')->required()->maxLength(100),
            Select::make('source')->label('Fuente')->options(['Amazon KDP' => 'Amazon KDP', 'Distribuidor externo' => 'Distribuidor externo', 'Registro manual' => 'Registro manual'])->default('Registro manual')->required(),
            TextInput::make('marketplace')->label('Marketplace o distribuidor')->required(),
            DatePicker::make('payment_date')->label('Fecha de pago'),
            Select::make('status')->label('Estado')->options(['paid' => 'Pagado', 'pending' => 'Pendiente', 'failed' => 'Fallido'])->default('paid')->required(),
            TextInput::make('currency')->label('Moneda')->default('EUR')->length(3)->required(),
            TextInput::make('payment_amount')->label('Importe pagado')->numeric()->step('0.0001'),
            TextInput::make('net_earnings')->label('Ingresos netos')->numeric()->step('0.0001'),
            TextInput::make('tax_withholding')->label('Retención fiscal')->numeric()->step('0.0001'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('payment_date', 'desc')->columns([
            Tables\Columns\TextColumn::make('payment_number')->label('Número')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('payment_date')->label('Fecha')->date('d/m/Y')->sortable(),
            Tables\Columns\TextColumn::make('payment_method')->label('Método')->sortable()->toggleable(),
            Tables\Columns\TextColumn::make('sales_period_start')->label('Periodo')->sortable()->formatStateUsing(fn ($state, KdpPayment $record) => $state ? $record->sales_period_start->format('m/Y') : '—'),
            Tables\Columns\TextColumn::make('marketplace')->label('Marketplace')->sortable(),
            Tables\Columns\TextColumn::make('source')->label('Fuente')->sortable()->toggleable(),
            Tables\Columns\TextColumn::make('net_earnings')->label('Neto')->money(fn ($record) => $record->currency)->sortable()->toggleable(),
            Tables\Columns\TextColumn::make('payment_amount')->label('Importe')->money(fn ($record) => $record->currency)->sortable(),
            Tables\Columns\TextColumn::make('tax_withholding')->label('Retención')->numeric(2)->sortable(),
            Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->sortable(),
            Tables\Columns\TextColumn::make('allocations_count')->counts('allocations')->label('Asignaciones')->sortable(),
            Tables\Columns\TextColumn::make('latestImportBatch.original_file_name')->label('Último informe')->sortable(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKdpPayments::route('/'),
            'create' => Pages\CreateKdpPayment::route('/create'),
            'edit' => Pages\EditKdpPayment::route('/{record}/edit'),
        ];
    }
}
