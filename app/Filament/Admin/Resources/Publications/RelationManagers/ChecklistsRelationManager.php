<?php

namespace App\Filament\Admin\Resources\Publications\RelationManagers;

use App\Filament\Admin\Resources\Checklists\ChecklistResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecklistsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklists';

    protected static ?string $title = 'Listas de verificación';

    protected static ?string $icon = 'heroicon-o-clipboard-document-check';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Lista')->searchable()->sortable(),
                TextColumn::make('progress_percentage')->label('Progreso')->suffix('%'),
                TextColumn::make('items_count')->label('Elementos')->counts('items'),
                TextColumn::make('created_at')->label('Creada')->dateTime()->sortable(),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nueva checklist para esta publicación')
                    ->icon('heroicon-o-plus')
                    ->url(fn (): string => ChecklistResource::getUrl('create', [
                        'work_id' => $this->getOwnerRecord()->work_id,
                        'publication_id' => $this->getOwnerRecord()->getKey(),
                    ])),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Abrir')
                    ->url(fn ($record): string => ChecklistResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('No hay checklists para esta publicación')
            ->emptyStateDescription('Las listas generales de la obra se mantienen separadas.')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
    }
}
