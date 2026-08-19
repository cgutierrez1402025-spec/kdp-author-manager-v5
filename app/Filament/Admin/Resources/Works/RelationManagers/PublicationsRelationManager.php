<?php

namespace App\Filament\Admin\Resources\Works\RelationManagers;

use App\Filament\Admin\Resources\Publications\PublicationResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PublicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'publications';

    protected static ?string $title = 'Publicaciones';

    protected static ?string $icon = 'heroicon-o-globe-alt';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('format')->label('Formato')->badge(),
                TextColumn::make('marketplace.name')->label('Marketplace'),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('asin')->label('ASIN')->copyable(),
                TextColumn::make('publication_date')->label('Publicación')->date(),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nueva publicación')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => PublicationResource::getUrl('create', ['work_id' => $this->getOwnerRecord()->getKey()])),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Gestionar')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record): string => PublicationResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Aún no hay publicaciones')
            ->emptyStateDescription('Prepara una edición cuando el manuscrito esté listo.')
            ->emptyStateIcon('heroicon-o-book-open');
    }
}
