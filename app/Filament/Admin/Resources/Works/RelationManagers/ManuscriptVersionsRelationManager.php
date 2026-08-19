<?php

namespace App\Filament\Admin\Resources\Works\RelationManagers;

use App\Filament\Admin\Resources\ManuscriptVersions\ManuscriptVersionResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManuscriptVersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'manuscriptVersions';

    protected static ?string $title = 'Manuscritos';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('version_number')
            ->columns([
                TextColumn::make('version_number')->label('Versión')->prefix('v')->sortable(),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('word_count')->label('Palabras')->numeric(),
                IconColumn::make('is_final')->label('Final')->boolean(),
                TextColumn::make('updated_at')->label('Actualizada')->since(),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nueva versión')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => ManuscriptVersionResource::getUrl('create', ['work_id' => $this->getOwnerRecord()->getKey()])),
            ])
            ->actions([
                Action::make('view')
                    ->label('Abrir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => ManuscriptVersionResource::getUrl('view', ['record' => $record])),
            ])
            ->emptyStateHeading('Sin versiones de manuscrito')
            ->emptyStateDescription('Crea la primera versión para comenzar el trabajo editorial.')
            ->emptyStateIcon('heroicon-o-document-plus');
    }
}
