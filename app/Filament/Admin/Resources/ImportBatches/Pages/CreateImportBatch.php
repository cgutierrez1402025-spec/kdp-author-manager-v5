<?php

namespace App\Filament\Admin\Resources\ImportBatches\Pages;

use App\Filament\Admin\Resources\ImportBatches\ImportBatchResource;
use App\Models\ImportBatch;
use App\Services\Kdp\KdpReportImportService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateImportBatch extends CreateRecord
{
    protected static string $resource = ImportBatchResource::class;

    protected static ?string $title = 'Cargar informe de Amazon KDP';

    protected function handleRecordCreation(array $data): Model
    {
        $path = $data['original_file_path'];
        $absolutePath = Storage::disk('local')->path($path);
        $hash = hash_file('sha256', $absolutePath);

        if (ImportBatch::where('file_hash', $hash)->exists()) {
            Storage::disk('local')->delete($path);
            throw ValidationException::withMessages([
                'data.original_file_path' => 'Este mismo archivo ya fue importado.',
            ]);
        }

        return ImportBatch::create([
            'user_id' => auth()->id(),
            'import_type' => $data['import_type'],
            'report_period' => Carbon::parse($data['report_period'])->startOfMonth(),
            'source_system' => 'amazon_kdp',
            'original_file_path' => $path,
            'original_file_name' => basename($path),
            'file_hash' => $hash,
            'detected_format' => strtolower(pathinfo($path, PATHINFO_EXTENSION)),
            'status' => 'pending',
            'processed_by_ai' => false,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    protected function afterCreate(): void
    {
        try {
            $batch = app(KdpReportImportService::class)->import($this->record);
            Notification::make()
                ->success()
                ->title('Informe KDP importado')
                ->body("{$batch->imported_rows} filas cargadas, {$batch->skipped_rows} duplicadas y {$batch->error_rows} con error.")
                ->send();
        } catch (Throwable $exception) {
            report($exception);
            Notification::make()
                ->danger()
                ->title('No se pudo importar el informe')
                ->body($exception->getMessage())
                ->persistent()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return ImportBatchResource::getUrl('index');
    }
}
