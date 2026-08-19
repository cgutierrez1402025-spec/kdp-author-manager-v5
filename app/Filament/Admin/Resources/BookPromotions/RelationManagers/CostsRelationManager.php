<?php

namespace App\Filament\Admin\Resources\BookPromotions\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CostsRelationManager extends RelationManager
{
    protected static string $relationship = 'costs';

    protected static ?string $recordTitleAttribute = 'cost_type';

    protected static ?string $title = 'Costos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('cost_type')
                    ->label('Tipo de Costo')
                    ->options([
                        'advertising' => 'Publicidad',
                        'promotion' => 'Promoción',
                        'marketing' => 'Marketing',
                        'tools' => 'Herramientas',
                        'other' => 'Otro',
                    ])
                    ->required(),

                TextInput::make('description')
                    ->label('Descripción')
                    ->maxLength(255),

                TextInput::make('amount')
                    ->label('Importe')
                    ->numeric()
                    ->step('0.01')
                    ->required(),

                TextInput::make('currency')
                    ->label('Moneda')
                    ->maxLength(3)
                    ->default('EUR')
                    ->required(),

                DatePicker::make('date')
                    ->label('Fecha')
                    ->required(),

                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cost_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'advertising' => 'primary',
                        'promotion' => 'warning',
                        'marketing' => 'info',
                        'tools' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('description')
                    ->label('Descripción')
                    ->limit(30),

                TextColumn::make('amount')
                    ->label('Importe')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
