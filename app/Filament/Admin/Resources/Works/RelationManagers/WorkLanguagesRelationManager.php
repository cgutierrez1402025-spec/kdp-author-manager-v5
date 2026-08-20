<?php

namespace App\Filament\Admin\Resources\Works\RelationManagers;

use App\Models\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkLanguagesRelationManager extends RelationManager
{
    protected static string $relationship = 'workLanguages';

    protected static ?string $title = 'Idiomas de la obra';

    protected static ?string $recordTitleAttribute = 'language_code';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('language_code')
                ->label('Idioma')
                ->options(fn () => Language::query()->where('is_active', true)->orderBy('name')->pluck('name', 'code'))
                ->searchable()
                ->required(),
            TextInput::make('regional_variant')->label('Variante regional')->helperText('Opcional, por ejemplo es-ES o en-US.'),
            TextInput::make('translated_title')->label('Título traducido'),
            Select::make('translation_status')->label('Estado de traducción')->options([
                'original' => 'Original', 'planned' => 'Planificada', 'in_progress' => 'En curso', 'complete' => 'Completada',
            ])->default('original')->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('language_code')->label('Idioma')->formatStateUsing(fn (string $state): string => Language::query()->where('code', $state)->value('name') ?? $state)->sortable(),
            TextColumn::make('regional_variant')->label('Variante')->sortable(),
            TextColumn::make('translated_title')->label('Título traducido')->sortable(),
            TextColumn::make('translation_status')->label('Estado')->badge()->sortable(),
        ])->headerActions([CreateAction::make()])->actions([EditAction::make(), DeleteAction::make()]);
    }
}
