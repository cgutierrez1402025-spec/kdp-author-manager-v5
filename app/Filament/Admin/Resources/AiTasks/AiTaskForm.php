<?php

namespace App\Filament\Admin\Resources\AiTasks;

use Filament\Forms\Form;

class AiTaskForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                // Aquí puedes definir tus campos, por ejemplo:
                Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nombre'),

                Filament\Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelada',
                    ])
                    ->default('pending')
                    ->required()
                    ->label('Estado'),

                // Otros campos según tus necesidades
            ]);
    }
}
