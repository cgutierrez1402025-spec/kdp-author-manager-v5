<?php

namespace App\Filament\Admin\Resources\Platforms\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarketplacesRelationManager extends RelationManager
{
    protected static string $relationship = 'marketplaces';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),

                TextInput::make('code')
                    ->label('Código')
                    ->required()
                    ->maxLength(50),

                TextInput::make('currency')
                    ->label('Moneda')
                    ->required()
                    ->maxLength(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->searchable(),

                TextColumn::make('currency')
                    ->label('Moneda')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('publications_count')
                    ->label('Publicaciones')
                    ->counts('publications'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                CreateAction::make(),
            ]);
    }
}
