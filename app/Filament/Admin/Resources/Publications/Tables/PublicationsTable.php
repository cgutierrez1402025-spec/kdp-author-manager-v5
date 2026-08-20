<?php

namespace App\Filament\Admin\Resources\Publications\Tables;

use App\Filament\Admin\Resources\Checklists\ChecklistResource;
use App\Filament\Admin\Resources\Tasks\TaskResource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PublicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['work', 'platform', 'marketplace', 'workLanguage', 'manuscriptVersion']))
            ->columns([
                TextColumn::make('work.title_public')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('format')
                    ->label('Formato')
                    ->badge()
                    ->sortable()
                    ->color(fn (?string $state): string => match ($state) {
                        'paperback' => 'primary',
                        'hardcover' => 'success',
                        'kindle' => 'warning',
                        'audiobook' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('platform.name')
                    ->label('Plataforma')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('marketplace.name')
                    ->label('Marketplace')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Borrador', 'processing' => 'Procesando', 'published' => 'Publicada', 'error' => 'Error', default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'draft' => 'gray',
                        'processing' => 'warning',
                        'published' => 'success',
                        'error' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label('Publicado')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('format')
                    ->label('Formato')
                    ->options([
                        'paperback' => 'Paperback',
                        'hardcover' => 'Hardcover',
                        'kindle' => 'Kindle',
                        'audiobook' => 'Audiobook',
                    ]),

                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'processing' => 'Procesando',
                        'published' => 'Publicado',
                        'error' => 'Error',
                    ]),
            ])
            ->actions([
                Action::make('createTask')
                    ->label('Nueva tarea')
                    ->icon('heroicon-o-check-circle')
                    ->url(fn ($record): string => TaskResource::getUrl('create', [
                        'work_id' => $record->work_id,
                        'publication_id' => $record->getKey(),
                    ])),
                Action::make('createChecklist')
                    ->label('Nueva checklist')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn ($record): string => ChecklistResource::getUrl('create', [
                        'work_id' => $record->work_id,
                        'publication_id' => $record->getKey(),
                    ])),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->emptyStateHeading('No hay publicaciones')
            ->emptyStateDescription('Crea una edición cuando el manuscrito esté listo para su distribución.')
            ->emptyStateIcon('heroicon-o-globe-alt')
            ->defaultSort('created_at', 'desc');
    }
}
