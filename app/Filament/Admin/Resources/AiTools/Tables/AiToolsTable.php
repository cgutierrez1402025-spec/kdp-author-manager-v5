<?php

namespace App\Filament\Admin\Resources\AiTools\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\RatingColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AiToolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('prompts'))
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('provider')
                    ->label('Proveedor')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'openai' => 'success',
                        'anthropic' => 'primary',
                        'google' => 'warning',
                        'cohere' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('model')
                    ->label('Modelo')
                    ->searchable(),

                RatingColumn::make('quality_score')
                    ->label('Calidad')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->label('Proveedor')
                    ->options([
                        'openai' => 'OpenAI',
                        'anthropic' => 'Anthropic',
                        'google' => 'Google',
                        'cohere' => 'Cohere',
                    ]),
            ])
            ->actions([
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
