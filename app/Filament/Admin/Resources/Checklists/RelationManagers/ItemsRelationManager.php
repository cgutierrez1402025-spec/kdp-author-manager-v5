<?php

namespace App\Filament\Admin\Resources\Checklists\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';  // ✅ Corregido: quitado el ?

    protected static ?string $recordTitleAttribute = 'item';

    protected static ?string $title = 'Elementos';  // ✅ Corregido: cambiar public a protected static

    public function form(Form $form): Form  // ✅ Corregido: Schema → Form
    {
        return $form
            ->schema([  // ✅ Corregido: components() → schema()
                TextInput::make('item')
                    ->label('Elemento')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_checked')
                    ->label('Completado')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item')
                    ->label('Elemento')
                    ->searchable(),

                IconColumn::make('is_checked')  // ✅ Corregido: BooleanColumn → IconColumn
                    ->label('Completado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('checkedBy.name')
                    ->label('Completado por')
                    ->placeholder('N/A'),
            ])
            ->headerActions([  // ✅ Corregido: toolbarActions() → headerActions()
                CreateAction::make(),
            ])
            ->actions([  // ✅ Corregido: recordActions() → actions()
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
