<?php

namespace App\Filament\Admin\Resources\ImportBatches\Pages;

use App\Filament\Admin\Resources\ImportBatches\ImportBatchResource;
use App\Services\Kdp\KdpBulkImportService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateImportBatch extends CreateRecord
{
    protected static string $resource = ImportBatchResource::class;

    protected static ?string $title = 'Cargar informe de Amazon KDP';

    protected function handleRecordCreation(array $data): Model
    {
        $session = app(KdpBulkImportService::class)->import(
            paths: array_values((array) $data['original_file_paths']),
            userId: (int) auth()->id(),
            fallbackPeriod: $data['report_period'] ?? null,
            requestedType: $data['import_type'],
            notes: $data['notes'] ?? null,
        );

        $batch = $session->batches()->first();
        if (! $batch) {
            throw ValidationException::withMessages([
                'data.original_file_paths' => 'Ningún archivo pudo añadirse. Comprueba si ya fueron importados.',
            ]);
        }

        session()->flash('bulk_import_summary', [
            'files' => $session->total_files, 'completed' => $session->completed_files,
            'failed' => $session->failed_files, 'duplicates' => $session->duplicate_files,
            'rows' => $session->imported_rows,
        ]);

        return $batch;
    }

    protected function afterCreate(): void
    {
        $summary = session()->pull('bulk_import_summary');
        Notification::make()->success()->title('Sesión de importación finalizada')
            ->body("{$summary['completed']} de {$summary['files']} archivos procesados; {$summary['rows']} filas nuevas, {$summary['duplicates']} archivos duplicados y {$summary['failed']} fallidos.")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return ImportBatchResource::getUrl('index');
    }
}
