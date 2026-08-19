<?php

namespace App\Filament\Admin\Resources\Publications\Pages;

use App\Filament\Admin\Resources\Publications\PublicationResource;
use App\Services\EditorialIntegrityService;
use App\Services\KdpApiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPublication extends EditRecord
{
    protected static string $resource = PublicationResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return app(EditorialIntegrityService::class)->validatePublication($data, auth()->user());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_with_kdp')
                ->label('Sincronizar con KDP')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn () => $this->record->asin !== null)
                ->action(function (KdpApiService $service) {
                    $result = $service->syncPublication($this->record);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Sincronización completada')
                            ->body('Datos actualizados desde Amazon KDP.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error de sincronización')
                            ->body($result['error'] ?? 'Error desconocido')
                            ->danger()
                            ->send();
                    }

                    $this->refreshFormData();
                }),

            Action::make('push_metadata')
                ->label('Enviar Metadatos a KDP')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn () => $this->record->asin !== null)
                ->requiresConfirmation()
                ->modalHeading('Confirmar envío de metadatos')
                ->modalDescription('Esto enviará los metadatos a KDP. La publicación pasará a estado "procesando".')
                ->action(function (KdpApiService $service) {
                    $result = $service->updateMetadata($this->record->id);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Metadatos enviados')
                            ->body($result['message'] ?? 'Metadatos enviados a KDP correctamente.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error al enviar')
                            ->body($result['error'] ?? 'Error desconocido')
                            ->danger()
                            ->send();
                    }

                    $this->refreshFormData();
                }),
        ];
    }
}
