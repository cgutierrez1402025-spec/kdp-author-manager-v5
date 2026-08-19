<?php

namespace App\Filament\Admin\Resources\Works\RelationManagers;

use App\Filament\Admin\Resources\Sources\SourceResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SourcesRelationManager extends RelationManager
{
    protected static string $relationship = 'sources';

    protected static ?string $title = 'Fuentes';

    protected static ?string $icon = 'heroicon-o-bookmark-square';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Fuente')->searchable(),
                TextColumn::make('source_type')->label('Tipo')->badge(),
                TextColumn::make('author')->label('Autor'),
                TextColumn::make('year')->label('Año'),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nueva fuente')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => SourceResource::getUrl('create', ['work_id' => $this->getOwnerRecord()->getKey()])),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Abrir')
                    ->url(fn ($record): string => SourceResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('No hay fuentes registradas')
            ->emptyStateIcon('heroicon-o-bookmark');
    }
}
