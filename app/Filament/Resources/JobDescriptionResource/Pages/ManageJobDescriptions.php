<?php

namespace App\Filament\Resources\JobDescriptionResource\Pages;

use App\Filament\Resources\JobDescriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJobDescriptions extends ManageRecords
{
    protected static string $resource = JobDescriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
