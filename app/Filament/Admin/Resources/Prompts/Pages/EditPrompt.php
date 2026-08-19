<?php

namespace App\Filament\Admin\Resources\Prompts\Pages;

use App\Filament\Admin\Resources\Prompts\PromptResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPrompt extends EditRecord
{
    protected static string $resource = PromptResource::class;

    protected static ?string $title = 'Editar Prompt';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('execute')
                ->label('Ejecutar Prompt')
                ->icon('heroicon-o-play')
                ->modalHeading('Ejecutar Prompt')
                ->modalDescription('El prompt será enviado a la IA seleccionada.')
                ->modalWidth('2xl')
                ->form([
                    Textarea::make('input_override')
                        ->label('Input adicional (opcional)')
                        ->rows(3)
                        ->placeholder('Añade contexto o instrucciones adicionales...'),
                ])
                ->action(function (array $data): array {
                    $prompt = $this->resolveRecord();

                    return $prompt->execute($data['input_override'] ?? '');
                })
                ->modalContent(function (array $data) {
                    if (! isset($data['result']['success'])) {
                        return null;
                    }

                    return view('filament.pages.prompt-result', [
                        'result' => $data['result'],
                        'prompt' => $this->resolveRecord(),
                    ]);
                })
                ->modalSubmitActionLabel('Guardar Resultado')
                ->after(function (array $data) {
                    if (! isset($data['result']['success']) || ! $data['result']['success']) {
                        return;
                    }

                    $prompt = $this->resolveRecord();
                    $prompt->update([
                        'result_text' => $data['result']['result'],
                    ]);

                    Notification::make()
                        ->title('Prompt ejecutado')
                        ->body('El resultado se ha guardado correctamente.')
                        ->success()
                        ->send();

                    $this->refreshFormData();
                }),

            EditAction::make(),
        ];
    }
}
