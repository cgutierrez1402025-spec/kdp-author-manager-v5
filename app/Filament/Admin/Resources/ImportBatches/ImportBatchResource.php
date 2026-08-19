<?php

namespace App\Filament\Admin\Resources\ImportBatches;

use App\Filament\Admin\Resources\ImportBatches\Pages\CreateImportBatch;
use App\Filament\Admin\Resources\ImportBatches\Pages\ListImportBatches;
use App\Models\ImportBatch;
use App\Services\Kdp\KdpReportImportService;
use App\Services\Kdp\KdpReportTypeDetector;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

class ImportBatchResource extends Resource
{
    protected static ?string $model = ImportBatch::class;

    protected static ?string $slug = 'importaciones-kdp';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Importar informes KDP';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $modelLabel = 'importación KDP';

    protected static ?string $pluralModelLabel = 'importaciones KDP';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informe de Amazon KDP')
                ->description('Selecciona varios CSV/XLSX o un ZIP. La aplicación detectará individualmente el tipo y el periodo de cada informe.')
                ->schema([
                    Forms\Components\Select::make('import_type')
                        ->label('Tipo de informe')
                        ->options([
                            'auto' => 'Detectar automáticamente',
                            'prior_royalties' => 'Regalías de meses anteriores',
                            'sales_royalties' => 'Ventas y regalías',
                            'orders' => 'Pedidos',
                            'kenp' => 'Páginas KENP leídas',
                            'payments' => 'Pagos',
                            'historical' => 'Histórico',
                        ])
                        ->default('auto')
                        ->helperText('Con varios archivos, cada uno se clasifica por sus cabeceras y hojas.')
                        ->required()
                        ->native(false),
                    Forms\Components\DatePicker::make('report_period')
                        ->label('Mes del informe')
                        ->helperText('Se rellena desde el nombre si todos los archivos indican el mismo mes. Sólo actúa como respaldo; cada archivo conserva su periodo detectado.')
                        ->displayFormat('m/Y'),
                    Forms\Components\FileUpload::make('original_file_paths')
                        ->label('Informes descargados de KDP')
                        ->disk('local')
                        ->directory('private/kdp-imports')
                        ->visibility('private')
                        ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => (string) Str::uuid().'-'.Str::of($file->getClientOriginalName())->replaceMatches('/[^A-Za-z0-9._-]/', '_'))
                        ->acceptedFileTypes([
                            'text/csv', 'text/plain',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/zip', 'application/x-zip-compressed',
                        ])
                        ->maxSize(100 * 1024)
                        ->multiple()
                        ->maxFiles(20)
                        ->reorderable()
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set): void {
                            $periods = collect((array) $state)
                                ->map(fn ($path) => app(KdpReportTypeDetector::class)->periodFromFilename(basename((string) $path)))
                                ->filter()->unique()->values();

                            if ($periods->count() === 1) {
                                $set('report_period', $periods->first());
                            } elseif ($periods->count() > 1) {
                                $set('report_period', null);
                            }
                        })
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
                Tables\Columns\TextColumn::make('import_session_id')->label('Sesión')->formatStateUsing(fn ($state) => $state ? '#'.$state : 'Individual')->badge(),
                Tables\Columns\TextColumn::make('original_file_name')->label('Archivo')->searchable(),
                Tables\Columns\TextColumn::make('import_type')->label('Informe')->formatStateUsing(fn (string $state) => match ($state) {
                    'prior_royalties' => 'Regalías anteriores',
                    'sales_royalties' => 'Ventas y regalías',
                    'orders' => 'Pedidos',
                    'kenp' => 'KENP',
                    'payments' => 'Pagos',
                    'historical' => 'Histórico',
                    'unknown' => 'Pendiente de revisión',
                    default => $state,
                }),
                Tables\Columns\TextColumn::make('report_period')->label('Periodo')->date('m/Y')->sortable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->color(fn (string $state) => match ($state) {
                    'completed' => 'success',
                    'failed' => 'danger',
                    'processing' => 'warning',
                    'needs_review' => 'warning',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('imported_rows')->label('Importadas')->numeric(),
                Tables\Columns\TextColumn::make('skipped_rows')->label('Duplicadas')->numeric(),
                Tables\Columns\TextColumn::make('error_rows')->label('Errores')->numeric()->color('danger'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha de carga')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('reprocess')
                    ->label('Reprocesar')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalDescription('Se reconstruirán filas, catálogo y pagos desde el archivo original. Si falla, la operación se revierte y se conservan todos los datos anteriores.')
                    ->action(function (ImportBatch $record): void {
                        try {
                            $batch = app(KdpReportImportService::class)->reprocess($record);
                            Notification::make()
                                ->success()
                                ->title('Informe reprocesado')
                                ->body("{$batch->imported_rows} filas importadas y {$batch->error_rows} errores.")
                                ->send();
                        } catch (Throwable $exception) {
                            report($exception);
                            Notification::make()
                                ->danger()
                                ->title('No se pudo reprocesar')
                                ->body($exception->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('import_session_id')->label('Sesión')->relationship('importSession', 'id'),
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
