<?php

namespace App\Filament\Admin\Resources\EventBooks;

use App\Filament\Admin\Resources\EventBooks\Pages\CreateEventBook;
use App\Filament\Admin\Resources\EventBooks\Pages\EditEventBook;
use App\Filament\Admin\Resources\EventBooks\Pages\ListEventBooks;
use App\Filament\Admin\Resources\EventBooks\Schemas\EventBookForm;
use App\Filament\Admin\Resources\EventBooks\Tables\EventBooksTable;
use App\Filament\Concerns\ScopesAuthorOwnedRecords;
use App\Models\EventBook;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class EventBookResource extends Resource
{
    use ScopesAuthorOwnedRecords;

    protected static string $authorOwnershipPath = 'work';

    protected static ?string $slug = 'event-books';

    protected static ?string $model = EventBook::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Libros en Eventos';

    protected static ?string $navigationGroup = 'Eventos';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return EventBookForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return EventBooksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventBooks::route('/'),
            'create' => CreateEventBook::route('/create'),
            'edit' => EditEventBook::route('/{record}/edit'),
        ];
    }
}
