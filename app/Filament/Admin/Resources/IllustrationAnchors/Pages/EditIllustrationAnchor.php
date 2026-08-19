<?php

namespace App\Filament\Admin\Resources\IllustrationAnchors\Pages;

use App\Filament\Admin\Resources\IllustrationAnchors\IllustrationAnchorResource;
use App\Services\IllustrationAnchoringService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditIllustrationAnchor extends EditRecord
{
    protected static string $resource = IllustrationAnchorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')
                ->label('Aplicar al Manuscrito')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Aplicar Ilustración')
                ->modalDescription('Se insertará la imagen en el manuscrito y se creará una nueva versión.')
                ->modalWidth('3xl')
                ->modalContent(function () {
                    $service = app(IllustrationAnchoringService::class);
                    $result = $service->previewInsertion($this->record);

                    return view('filament.pages.illustration-anchoring-preview', [
                        'result' => $result,
                        'anchor' => $this->record,
                    ]);
                })
                ->action(function () {
                    $service = app(IllustrationAnchoringService::class);
                    $result = $service->applyToManuscript($this->record);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Ilustración aplicada')
                            ->body('Se ha creado una nueva versión del manuscrito con la ilustración insertada.')
                            ->success()
                            ->send();

                        $this->refreshFormData();
                    } else {
                        Notification::make()
                            ->title('Error al aplicar')
                            ->body($result['error'] ?? 'Error desconocido')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
