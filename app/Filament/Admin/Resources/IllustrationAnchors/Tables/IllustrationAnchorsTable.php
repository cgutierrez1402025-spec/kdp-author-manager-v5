<?php

namespace App\Filament\Admin\Resources\IllustrationAnchors\Tables;

use App\Services\IllustrationAnchoringService;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IllustrationAnchorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['illustration', 'manuscriptVersion']))
            ->columns([
                TextColumn::make('illustration.title')
                    ->label('Ilustración')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('manuscriptVersion.name')
                    ->label('Versión Manuscrito')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('anchor_type')
                    ->label('Tipo')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('position_type')
                    ->label('Posición')
                    ->placeholder('N/A'),

                IconColumn::make('applied')
                    ->label('Aplicado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('applied')
                    ->label('Aplicado')
                    ->options([
                        true => 'Sí',
                        false => 'No',
                    ]),

                SelectFilter::make('anchor_type')
                    ->label('Tipo de Anclaje')
                    ->options([
                        'inline' => 'En línea',
                        'block' => 'Bloque',
                        'chapter_start' => 'Inicio de Capítulo',
                        'chapter_end' => 'Final de Capítulo',
                    ]),
            ])
            ->actions([
                Action::make('apply')
                    ->label('Aplicar al Manuscrito')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->modalHeading('Aplicar Ilustración')
                    ->modalDescription('Se insertará la imagen en el manuscrito y se creará una nueva versión.')
                    ->modalWidth('3xl')
                    ->modalContent(function (IllustrationAnchor $record) {
                        $service = app(IllustrationAnchoringService::class);
                        $result = $service->previewInsertion($record);

                        return view('filament.pages.illustration-anchoring-preview', [
                            'result' => $result,
                            'anchor' => $record,
                        ]);
                    })
                    ->action(function (IllustrationAnchor $record) {
                        $service = app(IllustrationAnchoringService::class);
                        $result = $service->applyToManuscript($record);

                        if ($result['success']) {
                            Notification::make()
                                ->title('Ilustración aplicada')
                                ->body('Se ha creado una nueva versión del manuscrito con la ilustración insertada.')
                                ->success()
                                ->send();

                            return redirect()->route('illustration-anchors.edit', $record->id);
                        }

                        Notification::make()
                            ->title('Error al aplicar')
                            ->body($result['error'] ?? 'Error desconocido')
                            ->danger()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
