<?php

namespace App\Filament\Admin\Resources\Works;

use App\Filament\Admin\Resources\Works\Pages\CreateWork;
use App\Filament\Admin\Resources\Works\Pages\EditWork;
use App\Filament\Admin\Resources\Works\Pages\ListWorks;
use App\Filament\Admin\Resources\Works\Pages\ViewWork;
use App\Filament\Admin\Resources\Works\Schemas\WorkForm;
use App\Filament\Admin\Resources\Works\Tables\WorksTable;
use App\Models\Work;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkResource extends Resource
{
    protected static ?string $slug = 'works';

    protected static ?string $model = Work::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'title_public';

    protected static ?string $navigationLabel = 'Obras';

    protected static ?string $navigationGroup = 'Catálogo editorial';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Form $form): Form
    {
        return WorkForm::configure($form);
    }

    public static function table(Table $table): Table
    {
        return WorksTable::configure($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Resumen editorial')
                ->schema([
                    TextEntry::make('title_public')->label('Título público'),
                    TextEntry::make('author_name')->label('Autor'),
                    TextEntry::make('status')->label('Estado')->badge(),
                    TextEntry::make('original_language')->label('Idioma')->badge(),
                    TextEntry::make('planned_publish_date')->label('Publicación prevista')->date(),
                    TextEntry::make('genre')->label('Género'),
                ])
                ->columns(3),
            Section::make('Ciclo editorial')
                ->description('Situación actual de la obra dentro del proceso de publicación.')
                ->schema([
                    TextEntry::make('status')
                        ->hiddenLabel()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'idea' => '1 de 5 · Idea',
                            'redaccion' => '2 de 5 · Redacción',
                            'revision' => '3 de 5 · Revisión',
                            'preparacion' => '4 de 5 · Preparación',
                            'publicada' => '5 de 5 · Publicada',
                            default => ucfirst($state),
                        })
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'idea' => 'gray',
                            'redaccion' => 'warning',
                            'revision' => 'info',
                            'preparacion' => 'primary',
                            'publicada' => 'success',
                            default => 'gray',
                        }),
                ]),
            Section::make('Progreso')
                ->schema([
                    TextEntry::make('publications_count')
                        ->label('Publicaciones')
                        ->state(fn (Work $record): int => $record->publications()->count()),
                    TextEntry::make('manuscript_versions_count')
                        ->label('Versiones de manuscrito')
                        ->state(fn (Work $record): int => $record->manuscriptVersions()->count()),
                    TextEntry::make('tasks_count')
                        ->label('Tareas')
                        ->state(fn (Work $record): int => $record->tasks()->count()),
                    TextEntry::make('checklists_count')
                        ->label('Listas de control')
                        ->state(fn (Work $record): int => $record->checklists()->count()),
                ])
                ->columns(4),
            Section::make('Descripción comercial')
                ->schema([
                    TextEntry::make('description_marketing')
                        ->label('Descripción')
                        ->placeholder('Todavía no se ha añadido una descripción comercial.'),
                ]),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return auth()->user()?->canViewAllAuthorData()
            ? $query
            : $query->where('user_id', auth()->id());
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ManuscriptVersionsRelationManager::class,
            RelationManagers\PublicationsRelationManager::class,
            RelationManagers\TasksRelationManager::class,
            RelationManagers\SourcesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorks::route('/'),
            'create' => CreateWork::route('/create'),
            'edit' => EditWork::route('/{record}/edit'),
            'view' => ViewWork::route('/{record}'),
        ];
    }
}
