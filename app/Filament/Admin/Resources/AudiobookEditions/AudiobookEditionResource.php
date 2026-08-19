<?php

namespace App\Filament\Admin\Resources\AudiobookEditions;

use App\Filament\Admin\Resources\AudiobookEditions\Pages\CreateAudiobookEdition;
use App\Filament\Admin\Resources\AudiobookEditions\Pages\EditAudiobookEdition;
use App\Filament\Admin\Resources\AudiobookEditions\Pages\ListAudiobookEditions;
use App\Models\AudiobookEdition;
use App\Models\Work;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AudiobookEditionResource extends Resource
{
    protected static ?string $model = AudiobookEdition::class;

    protected static ?string $slug = 'audiolibros';

    protected static ?string $navigationIcon = 'heroicon-o-speaker-wave';

    protected static ?string $navigationLabel = 'Audiolibros';

    protected static ?string $navigationGroup = 'Publicaciones';

    protected static ?string $modelLabel = 'edición de audiolibro';

    protected static ?string $pluralModelLabel = 'ediciones de audiolibro';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Edición')->schema([
                Forms\Components\Select::make('work_id')->label('Obra')->options(fn () => Work::query()->when(! auth()->user()?->canViewAllAuthorData(), fn ($q) => $q->where('user_id', auth()->id()))->pluck('title', 'id'))->searchable()->required(),
                Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(255),
                Forms\Components\TextInput::make('language_code')->label('Idioma')->maxLength(5),
                Forms\Components\Select::make('production_method')->label('Producción')->options(['human' => 'Narración humana', 'virtual_voice' => 'Voz virtual', 'voice_replica' => 'Réplica de voz', 'hybrid' => 'Híbrida'])->required(),
                Forms\Components\Select::make('status')->label('Estado')->options(['idea' => 'Idea', 'rights_review' => 'Revisión de derechos', 'production' => 'Producción', 'quality_review' => 'Control de calidad', 'ready' => 'Lista', 'published' => 'Publicada', 'withdrawn' => 'Retirada', 'cancelled' => 'Cancelada'])->required(),
                Forms\Components\Select::make('rights_status')->label('Derechos de audio')->options(['pending' => 'Pendientes', 'confirmed' => 'Confirmados', 'restricted' => 'Restringidos', 'expired' => 'Expirados'])->required(),
                Forms\Components\Toggle::make('exclusive')->label('Distribución exclusiva'),
                Forms\Components\TextInput::make('estimated_duration_minutes')->label('Duración estimada (min)')->numeric()->minValue(1),
                Forms\Components\TextInput::make('final_duration_minutes')->label('Duración final (min)')->numeric()->minValue(1),
                Forms\Components\TextInput::make('list_price')->label('Precio')->numeric()->prefix('€'),
                Forms\Components\TextInput::make('currency')->label('Moneda')->maxLength(3),
                Forms\Components\TextInput::make('royalty_rate')->label('Regalía (%)')->numeric()->minValue(0)->maxValue(100),
                Forms\Components\Textarea::make('notes')->label('Notas')->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->label('Audiolibro')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('work.title')->label('Obra')->searchable(),
            Tables\Columns\TextColumn::make('production_method')->label('Producción')->badge(),
            Tables\Columns\TextColumn::make('status')->label('Estado')->badge(),
            Tables\Columns\TextColumn::make('rights_status')->label('Derechos')->badge(),
            Tables\Columns\TextColumn::make('final_duration_minutes')->label('Minutos')->numeric(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->when(! auth()->user()?->canViewAllAuthorData(), fn (Builder $q) => $q->where('user_id', auth()->id()));
    }

    public static function getPages(): array
    {
        return ['index' => ListAudiobookEditions::route('/'), 'create' => CreateAudiobookEdition::route('/create'), 'edit' => EditAudiobookEdition::route('/{record}/edit')];
    }
}
