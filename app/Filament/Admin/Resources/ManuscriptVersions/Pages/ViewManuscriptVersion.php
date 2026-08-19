<?php

namespace App\Filament\Admin\Resources\ManuscriptVersions\Pages;

use App\Filament\Admin\Resources\ManuscriptVersions\ManuscriptVersionResource;
use App\Filament\Admin\Resources\ManuscriptVersions\Schemas\ManuscriptVersionForm;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;

class ViewManuscriptVersion extends ViewRecord
{
    protected static string $resource = ManuscriptVersionResource::class;

    public function form(Form $form): Form
    {
        return ManuscriptVersionForm::configure($form);
    }
}
