<?php

namespace App\Filament\Admin\Resources\Narrators;

use App\Filament\Admin\Resources\Narrators\Pages\CreateNarrator;
use App\Filament\Admin\Resources\Narrators\Pages\EditNarrator;
use App\Filament\Admin\Resources\Narrators\Pages\ListNarrators;
use App\Models\Narrator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NarratorResource extends Resource
{
    protected static ?string $model = Narrator::class;

    protected static ?string $slug = 'narradores';

    protected static ?string $navigationIcon = 'heroicon-o-microphone';

    protected static ?string $navigationLabel = 'Narradores y voces';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $modelLabel = 'narrador o voz';

    protected static ?string $pluralModelLabel = 'narradores y voces';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nombre')->required(),
            Forms\Components\TextInput::make('stage_name')->label('Nombre artístico'),
            Forms\Components\Select::make('narrator_type')->label('Tipo')->options(['human' => 'Persona', 'virtual_voice' => 'Voz virtual', 'voice_replica' => 'Réplica de voz'])->required(),
            Forms\Components\TextInput::make('provider')->label('Proveedor'),
            Forms\Components\TextInput::make('contact_email')->label('Correo')->email(),
            Forms\Components\TextInput::make('external_profile_url')->label('Perfil externo')->url(),
            Forms\Components\TagsInput::make('languages')->label('Idiomas'),
            Forms\Components\Toggle::make('voice_consent')->label('Consentimiento para uso/replicación de voz'),
            Forms\Components\DatePicker::make('consent_expires_at')->label('Caducidad del consentimiento'),
            Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('stage_name')->label('Nombre artístico')->sortable(),
            Tables\Columns\TextColumn::make('narrator_type')->label('Tipo')->badge()->sortable(),
            Tables\Columns\IconColumn::make('voice_consent')->label('Consentimiento')->boolean()->sortable(),
            Tables\Columns\TextColumn::make('consent_expires_at')->label('Caduca')->date('d/m/Y')->sortable(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return ['index' => ListNarrators::route('/'), 'create' => CreateNarrator::route('/create'), 'edit' => EditNarrator::route('/{record}/edit')];
    }
}
