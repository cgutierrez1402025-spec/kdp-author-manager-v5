<?php

namespace App\Filament\Admin\Resources\Languages;

use App\Models\Language;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static ?string $slug = 'languages';

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationLabel = 'Idiomas';

    protected static ?string $navigationGroup = 'Configuración editorial';

    protected static ?string $modelLabel = 'idioma';

    protected static ?string $pluralModelLabel = 'idiomas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('code')->label('Código ISO')->helperText('Código de idioma de dos letras, por ejemplo es o en.')->required()->length(2)->unique(ignoreRecord: true),
            TextInput::make('name')->label('Nombre')->required()->maxLength(100),
            Toggle::make('is_active')->label('Disponible en los desplegables')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->label('Código')->sortable()->searchable(),
            TextColumn::make('name')->label('Idioma')->sortable()->searchable(),
            IconColumn::make('is_active')->label('Activo')->boolean()->sortable(),
            TextColumn::make('created_at')->label('Creado')->dateTime()->sortable(),
        ])->headerActions([CreateAction::make()])->actions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}
