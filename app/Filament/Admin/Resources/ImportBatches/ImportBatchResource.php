<?php

namespace App\Filament\Admin\Resources\ImportBatches;

use App\Filament\Admin\Resources\ImportBatches\Pages\CreateImportBatch;
use App\Filament\Admin\Resources\ImportBatches\Pages\ListImportBatches;
use App\Models\ImportBatch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImportBatchResource extends Resource
{
    protected static ?string $model = ImportBatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Importar informes KDP';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $modelLabel = 'importación KDP';

    protected static ?string $pluralModelLabel = 'importaciones KDP';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informe de Amazon KDP')
                ->description('Descarga el informe desde kdpreports.amazon.com y súbelo sin modificarlo. Se admiten CSV y XLSX.')
                ->schema([
                    Forms\Components\Select::make('import_type')
                        ->label('Tipo de informe')
                        ->options([
                            'prior_royalties' => 'Regalías de meses anteriores',
                            'sales_royalties' => 'Ventas y regalías',
                            'orders' => 'Pedidos',
                            'kenp' => 'Páginas KENP leídas',
                            'payments' => 'Pagos',
                            'historical' => 'Histórico',
                        ])
                        ->required()
                        ->native(false),
                    Forms\Components\DatePicker::make('report_period')
                        ->label('Mes del informe')
                        ->helperText('Selecciona cualquier día del mes; se guardará el primer día. Es necesario para identificar reimportaciones.')
                        ->required()
                        ->displayFormat('m/Y'),
                    Forms\Components\FileUpload::make('original_file_path')
                        ->label('Archivo descargado de KDP')
                        ->disk('local')
                        ->directory('private/kdp-imports')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'text/csv', 'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(20 * 1024)
                        ->required()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('original_file_name')->label('Archivo')->searchable(),
                Tables\Columns\TextColumn::make('import_type')->label('Informe')->formatStateUsing(fn (string $state) => match ($state) {
                    'prior_royalties' => 'Regalías anteriores',
                    'sales_royalties' => 'Ventas y regalías',
                    'orders' => 'Pedidos',
                    'kenp' => 'KENP',
                    'payments' => 'Pagos',
                    'historical' => 'Histórico',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('report_period')->label('Periodo')->date('m/Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->color(fn (string $state) => match ($state) {
                    'completed' => 'success',
                    'failed' => 'danger',
                    'processing' => 'warning',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('imported_rows')->label('Importadas')->numeric(),
                Tables\Columns\TextColumn::make('skipped_rows')->label('Duplicadas')->numeric(),
                Tables\Columns\TextColumn::make('error_rows')->label('Errores')->numeric()->color('danger'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha de carga')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->emptyStateHeading('Todavía no hay informes KDP')
            ->emptyStateDescription('Carga un CSV o XLSX descargado de Amazon KDP para llenar las tablas de análisis.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImportBatches::route('/'),
            'create' => CreateImportBatch::route('/create'),
        ];
    }
}
