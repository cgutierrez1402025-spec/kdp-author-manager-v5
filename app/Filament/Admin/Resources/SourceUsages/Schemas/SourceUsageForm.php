<?php

namespace App\Filament\Admin\Resources\SourceUsages\Schemas;

use App\Models\Source;
use Filament\Forms;
use Filament\Forms\Form;

class SourceUsageForm
{
    public static function configure(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Uso de Fuente')
                    ->schema([
                        Forms\Components\Select::make('source_id')
                            ->relationship('source', 'title', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->whereHas('work', fn ($work) => $work->where('user_id', auth()->id()))
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, $set): void {
                                $set('work_id', $state ? Source::find($state)?->work_id : null);
                                $set('manuscript_version_id', null);
                                $set('chapter_id', null);
                            })
                            ->label('Fuente')
                            ->required(),

                        Forms\Components\Select::make('work_id')
                            ->relationship('work', 'title_public', modifyQueryUsing: fn ($query) => auth()->user()?->hasRole('admin') ? $query : $query->where('user_id', auth()->id())
                            )
                            ->disabled(fn ($get): bool => filled($get('source_id')))
                            ->dehydrated()
                            ->live()
                            ->label('Obra')
                            ->required(),

                        Forms\Components\Select::make('manuscript_version_id')
                            ->relationship('manuscriptVersion', 'version_number', modifyQueryUsing: fn ($query, $get) => $query->when($get('work_id'), fn ($q, $workId) => $q->where('work_id', $workId))
                            )
                            ->live()
                            ->afterStateUpdated(fn ($set) => $set('chapter_id', null))
                            ->label('Versión Manuscrito')
                            ->nullable(),

                        Forms\Components\Select::make('chapter_id')
                            ->relationship('chapter', 'title', modifyQueryUsing: fn ($query, $get) => $query->when($get('manuscript_version_id'), fn ($q, $versionId) => $q->where('manuscript_version_id', $versionId))
                            )
                            ->label('Capítulo')
                            ->nullable(),

                        Forms\Components\TextInput::make('usage_type')
                            ->label('Tipo de Uso')
                            ->maxLength(100),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Fragmento')
                    ->schema([
                        Forms\Components\Textarea::make('fragment')
                            ->label('Fragmento')
                            ->rows(4),

                        Forms\Components\Toggle::make('verified')
                            ->label('Verificado')
                            ->default(false),
                    ]),

                Forms\Components\Section::make('Notas')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas')
                            ->rows(2),
                    ]),
            ]);
    }
}
