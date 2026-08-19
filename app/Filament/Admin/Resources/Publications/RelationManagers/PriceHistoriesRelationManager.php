<?php

namespace App\Filament\Admin\Resources\Publications\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PriceHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'priceHistories';

    protected static ?string $title = 'Histórico de precios';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('marketplace_id')->relationship('marketplace', 'name', modifyQueryUsing: fn ($query) => $query->where('platform_id', $this->getOwnerRecord()->platform_id))->required(),
            Forms\Components\TextInput::make('price')->label('Precio')->numeric()->minValue(0)->required(),
            Forms\Components\TextInput::make('currency')->label('Moneda')->maxLength(3)->required(),
            Forms\Components\DatePicker::make('starts_at')->label('Desde')->required(),
            Forms\Components\DatePicker::make('ends_at')->label('Hasta'),
            Forms\Components\Select::make('change_reason')->label('Motivo')->options(['launch' => 'Lanzamiento', 'promotion' => 'Promoción', 'regular_adjustment' => 'Ajuste ordinario', 'other' => 'Otro']),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->defaultSort('starts_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('marketplace.name')->label('Mercado'),
            Tables\Columns\TextColumn::make('price')->label('Precio')->money(fn ($record) => $record->currency),
            Tables\Columns\TextColumn::make('starts_at')->label('Desde')->date('d/m/Y'),
            Tables\Columns\TextColumn::make('ends_at')->label('Hasta')->date('d/m/Y')->placeholder('Vigente'),
            Tables\Columns\TextColumn::make('change_reason')->label('Motivo')->badge(),
        ])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
