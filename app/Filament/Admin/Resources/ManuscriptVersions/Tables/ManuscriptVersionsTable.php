<?php

namespace App\Filament\Admin\Resources\ManuscriptVersions\Tables;

use App\Filament\Admin\Resources\ManuscriptVersions\ManuscriptVersionResource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ManuscriptVersionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['work', 'workLanguage', 'edition', 'creator']))
            ->columns([
                TextColumn::make('work.title_public')
                    ->label('Obra')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('version_number')
                    ->label('Versión')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('workLanguage.language_code')
                    ->label('Idioma')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('parent_version_number')
                    ->label('Padre')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($record) => $record->parentVersion->version_number ?? '-')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'draft' => 'Borrador', 'review' => 'Revisión', 'final' => 'Final', 'published' => 'Publicada', default => ucfirst((string) $state),
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'draft' => 'gray',
                        'review' => 'warning',
                        'final' => 'primary',
                        'published' => 'success',
                        default => 'gray',
                    }),

                ToggleColumn::make('is_candidate')
                    ->label('Candidata')
                    ->toggleable(isToggledHiddenByDefault: true),

                ToggleColumn::make('is_final')
                    ->label('Final'),

                ToggleColumn::make('is_published')
                    ->label('Publicada'),

                TextColumn::make('word_count')
                    ->label('Palabras')
                    ->numeric(),

                TextColumn::make('chapter_count')
                    ->label('Capítulos')
                    ->numeric(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'draft' => 'Borrador',
                        'review' => 'Revisión',
                        'final' => 'Final',
                        'published' => 'Publicado',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('createVersion')
                    ->label('Crear Nueva Versión')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function ($record) {
                        $newVersion = $record->createChildVersion([
                            'html_content' => $record->html_content,
                            'notes' => 'Clonado de versión '.$record->version_number,
                        ]);

                        return redirect()->to(ManuscriptVersionResource::getUrl('edit', ['record' => $newVersion]));
                    })
                    ->visible(fn ($record) => ! $record->is_published),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->emptyStateHeading('No hay versiones de manuscrito')
            ->emptyStateDescription('Crea una versión para comenzar el historial editorial.')
            ->emptyStateIcon('heroicon-o-document-duplicate')
            ->defaultSort('created_at', 'desc');
    }
}
