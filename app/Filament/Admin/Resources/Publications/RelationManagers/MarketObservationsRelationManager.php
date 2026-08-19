<?php

namespace App\Filament\Admin\Resources\Publications\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MarketObservationsRelationManager extends RelationManager
{
    protected static string $relationship = 'marketObservations';

    protected static ?string $title = 'Evolución en el mercado';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('marketplace_id')->relationship('marketplace', 'name', modifyQueryUsing: fn ($query) => $query->where('platform_id', $this->getOwnerRecord()->platform_id))->required(),
            Forms\Components\DatePicker::make('observed_at')->label('Fecha de observación')->required(),
            Forms\Components\TextInput::make('average_rating')->label('Valoración media')->numeric()->minValue(0)->maxValue(5),
            Forms\Components\TextInput::make('rating_count')->label('Valoraciones')->numeric()->minValue(0),
            Forms\Components\TextInput::make('review_count')->label('Reseñas')->numeric()->minValue(0),
            Forms\Components\TextInput::make('overall_rank')->label('Ranking general')->numeric()->minValue(1),
            Forms\Components\TextInput::make('category_name')->label('Categoría'),
            Forms\Components\TextInput::make('category_rank')->label('Ranking de categoría')->numeric()->minValue(1),
            Forms\Components\TextInput::make('source')->label('Fuente')->maxLength(255),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->defaultSort('observed_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('observed_at')->label('Fecha')->date('d/m/Y'),
            Tables\Columns\TextColumn::make('marketplace.name')->label('Mercado'),
            Tables\Columns\TextColumn::make('average_rating')->label('Valoración')->numeric(2),
            Tables\Columns\TextColumn::make('review_count')->label('Reseñas')->numeric(),
            Tables\Columns\TextColumn::make('overall_rank')->label('Ranking')->numeric(),
            Tables\Columns\TextColumn::make('category_rank')->label('Ranking categoría')->numeric(),
        ])->headerActions([Tables\Actions\CreateAction::make()])->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
