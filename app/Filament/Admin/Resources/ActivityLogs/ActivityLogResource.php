<?php

namespace App\Filament\Admin\Resources\ActivityLogs;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Logs';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}
