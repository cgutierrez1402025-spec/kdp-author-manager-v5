<?php

namespace App\Filament\Admin\Widgets;

use App\Models\BookEvent;
use Filament\Widgets\Widget;

class UpcomingEventsWidget extends Widget
{
    protected static string $view = 'filament.widgets.upcoming-events';

    protected static ?int $sort = 3;

    protected int $eventsLimit = 5;

    protected int|string|array $columnSpan = 1;

    public function getEventsProperty(): array
    {
        $user = auth()->user();

        return BookEvent::upcoming(30)
            ->when(! $user->canViewAllAuthorData(), fn ($query) => $query->where('user_id', $user->getKey()))
            ->orderBy('event_date')
            ->limit($this->eventsLimit)
            ->get()
            ->map(fn (BookEvent $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'event_date' => $event->event_date,
                'location_name' => $event->location_name,
                'city' => $event->city,
                'total_copies_sold' => $event->total_copies_sold,
                'total_income' => $event->total_income,
            ])
            ->values()
            ->all();
    }
}
