<?php

namespace App\Filament\Admin\Resources\ImportSessions;

use App\Filament\Admin\Resources\ImportSessions\Pages\ListImportSessions;
use App\Models\ImportSession;
use App\Services\Kdp\KdpBulkImportService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ImportSessionResource extends Resource
{
    protected static ?string $model = ImportSession::class;

    protected static ?string $slug = 'sesiones-importacion-kdp';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Sesiones de importación';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $modelLabel = 'sesión de importación';

    protected static ?string $pluralModelLabel = 'sesiones de importación';

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('id')->label('Sesión')->formatStateUsing(fn ($state) => '#'.$state),
            Tables\Columns\TextColumn::make('status')->label('Estado')->badge()->color(fn (string $state) => match ($state) {
                'completed' => 'success', 'partial' => 'warning', 'failed' => 'danger', default => 'gray'
            }),
            Tables\Columns\TextColumn::make('total_files')->label('Archivos')->numeric(),
            Tables\Columns\TextColumn::make('completed_files')->label('Completados')->numeric()->color('success'),
            Tables\Columns\TextColumn::make('duplicate_files')->label('Duplicados')->numeric(),
            Tables\Columns\TextColumn::make('failed_files')->label('Fallidos')->numeric()->color('danger'),
            Tables\Columns\TextColumn::make('imported_rows')->label('Filas nuevas')->numeric(),
            Tables\Columns\TextColumn::make('error_rows')->label('Errores')->numeric(),
            Tables\Columns\TextColumn::make('created_at')->label('Inicio')->dateTime('d/m/Y H:i'),
        ])->actions([
            Tables\Actions\Action::make('batches')->label('Ver archivos')->icon('heroicon-o-document-magnifying-glass')
                ->url(fn (ImportSession $record) => route('filament.admin.resources.importaciones-kdp.index', ['tableFilters[import_session_id][value]' => $record->id])),
            Tables\Actions\Action::make('reprocess')->label('Reprocesar sesión')->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()->modalDescription('Cada archivo se reconstruirá de forma atómica. Si uno falla, conservará sus datos anteriores y los demás continuarán.')
                ->action(function (ImportSession $record): void {
                    $session = app(KdpBulkImportService::class)->reprocessSession($record);
                    Notification::make()->title('Reprocesado finalizado')
                        ->body("{$session->completed_files} archivos correctos y {$session->failed_files} fallidos; {$session->imported_rows} filas reconstruidas.")
                        ->color($session->failed_files ? 'warning' : 'success')->send();
                }),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $query) => $query->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return ['index' => ListImportSessions::route('/')];
    }
}
