<?php

namespace App\Filament\Admin\Resources\Works\Pages;

use App\Filament\Admin\Resources\ManuscriptVersions\ManuscriptVersionResource;
use App\Filament\Admin\Resources\Publications\PublicationResource;
use App\Filament\Admin\Resources\Works\WorkResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWork extends ViewRecord
{
    protected static string $resource = WorkResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Resumen';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newManuscriptVersion')
                ->label('Nueva versión')
                ->icon('heroicon-o-document-plus')
                ->url(fn (): string => ManuscriptVersionResource::getUrl('create', ['work_id' => $this->record->getKey()])),
            Action::make('newPublication')
                ->label('Nueva publicación')
                ->icon('heroicon-o-book-open')
                ->url(fn (): string => PublicationResource::getUrl('create', ['work_id' => $this->record->getKey()])),
            EditAction::make(),
        ];
    }
}
