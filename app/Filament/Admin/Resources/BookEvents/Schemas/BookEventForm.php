<?php

namespace App\Filament\Admin\Resources\BookEvents\Schemas;

use Filament\Forms;
use Filament\Forms\Form;

class BookEventForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles del Evento')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('event_type')
                            ->label('Tipo')
                            ->options([
                                'book_fair' => 'Feria del Libro',
                                'signing' => 'Firma de Libros',
                                'presentation' => 'Presentación',
                                'conference' => 'Conferencia',
                                'other' => 'Otro',
                            ])
                            ->required(),

                        Forms\Components\DatePicker::make('event_date')
                            ->label('Fecha')
                            ->required(),

                        Forms\Components\TextInput::make('start_time')
                            ->label('Hora Inicio'),

                        Forms\Components\TextInput::make('end_time')
                            ->label('Hora Fin'),

                        Forms\Components\TextInput::make('location_name')
                            ->label('Lugar')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('city')
                            ->label('Ciudad')
                            ->maxLength(100),

                        Forms\Components\TextInput::make('country')
                            ->label('País')
                            ->maxLength(100),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Organización')
                    ->schema([
                        Forms\Components\TextInput::make('organizer')
                            ->label('Organizador')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('contact_person')
                            ->label('Contacto')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email(),

                        Forms\Components\TextInput::make('expected_attendance')
                            ->label('Asistencia Estimada')
                            ->numeric()
                            ->nullable(),

                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'planned' => 'Planeado',
                                'confirmed' => 'Confirmado',
                                'completed' => 'Completado',
                                'cancelled' => 'Cancelado',
                            ])
                            ->default('planned')
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),
            ]);
    }
}
