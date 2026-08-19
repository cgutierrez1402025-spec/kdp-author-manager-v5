<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\BookPromotions\BookPromotionResource;
use App\Filament\Admin\Resources\Tasks\TaskResource;
use App\Filament\Admin\Resources\Works\WorkResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Inicio';

    public function getTitle(): string|Htmlable
    {
        return 'Resumen editorial';
    }

    public function getSubheading(): ?string
    {
        return 'Prioridades, rendimiento y próximos pasos de tu catálogo.';
    }

    public function getColumns(): int
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('newWork')
                ->label('Nueva obra')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url(WorkResource::getUrl('create')),
            Action::make('tasks')
                ->label('Ver tareas')
                ->icon('heroicon-o-check-circle')
                ->color('gray')
                ->url(TaskResource::getUrl()),
            Action::make('promotions')
                ->label('Promociones')
                ->icon('heroicon-o-megaphone')
                ->color('gray')
                ->url(BookPromotionResource::getUrl()),
        ];
    }
}
