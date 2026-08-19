<?php

namespace App\Filament\Admin\Resources\Publications\RelationManagers;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KdpMetadataRelationManager extends RelationManager
{
    protected static string $relationship = 'kdpMetadata';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Metadatos KDP';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Título')
                    ->maxLength(255),

                TextInput::make('subtitle')
                    ->label('Subtítulo')
                    ->maxLength(255),

                TextInput::make('author')
                    ->label('Autor')
                    ->maxLength(255),

                TextInput::make('series_name')
                    ->label('Nombre de Serie')
                    ->maxLength(255),

                TextInput::make('series_number')
                    ->label('Número de Serie')
                    ->numeric(),

                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(4),

                TextInput::make('keywords')
                    ->label('Palabras Clave')
                    ->maxLength(255),

                TextInput::make('age_range')
                    ->label('Rango de Edad')
                    ->maxLength(50),

                Textarea::make('rights')
                    ->label('Derechos')
                    ->rows(2),

                Textarea::make('ai_declaration')
                    ->label('Declaración IA')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),

                TextColumn::make('subtitle')
                    ->label('Subtítulo')
                    ->searchable(),

                TextColumn::make('author')
                    ->label('Autor'),

                TextColumn::make('series_name')
                    ->label('Serie'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                CreateAction::make()
                    ->slideOver(),
            ]);
    }
}
