<?php

namespace App\Filament\Admin\Resources\Works\Widgets;

use App\Models\Tag;
use App\Models\Work;
use App\Services\AiService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class AiToolsWidget extends Widget
{
    protected static string $view = 'filament.widgets.ai-tools';

    public ?Work $record = null;

    protected function getViewData(): array
    {
        return [
            'record' => $this->record,
        ];
    }

    protected function actions(): array
    {
        return [
            Action::make('suggest_tags')
                ->label('Sugerir Etiquetas')
                ->icon('heroicon-o-tag')
                ->form([
                    TextInput::make('title')
                        ->label('Título')
                        ->default($this->record?->title_public),
                    Textarea::make('description')
                        ->label('Descripción')
                        ->default($this->record?->description_marketing),
                ])
                ->action(function (array $data, AiService $aiService) {
                    $result = $aiService->suggestTags($data['title'], $data['description']);

                    if ($result['success']) {
                        $tags = array_map('trim', explode(',', $result['result']));

                        foreach ($tags as $tagName) {
                            if ($tag = Tag::firstOrCreate(['name' => $tagName])) {
                                $this->record->tags()->attach($tag->id);
                            }
                        }

                        Notification::make()
                            ->title('Etiquetas sugeridas y aplicadas')
                            ->body($result['result'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error')
                            ->body($result['error'])
                            ->danger()
                            ->send();
                    }

                    return $result;
                }),
        ];
    }
}
