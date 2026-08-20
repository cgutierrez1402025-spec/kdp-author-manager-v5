<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class Help extends Page
{
    protected static string $view = 'filament.admin.help';

    protected static ?string $title = 'Ayuda de la aplicación';

    protected static ?string $navigationLabel = 'Ayuda';

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Documentación';
}
