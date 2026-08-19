<?php

namespace App\Filament\Admin\Resources\BookEvents;

use App\Filament\Admin\Resources\BookEvents\Pages\CreateBookEvent;
use App\Filament\Admin\Resources\BookEvents\Pages\EditBookEvent;
use App\Filament\Admin\Resources\BookEvents\Pages\ListBookEvents;
use App\Filament\Admin\Resources\BookEvents\Schemas\BookEventForm;
use App\Filament\Admin\Resources\BookEvents\Tables\BookEventsTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\BookEvent;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class BookEventResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = '@user_id';

    protected static ?string $slug = 'book-events';

    protected static ?string $model = BookEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Calendario de eventos';

    protected static ?string $navigationGroup = 'Eventos';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return BookEventForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return BookEventsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookEvents::route('/'),
            'create' => CreateBookEvent::route('/create'),
            'edit' => EditBookEvent::route('/{record}/edit'),
        ];
    }
}
