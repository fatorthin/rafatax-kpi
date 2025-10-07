<?php

namespace App\Filament\Resources\DepartmentReferenceResource\Pages;

use App\Filament\Resources\DepartmentReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDepartmentReference extends ViewRecord
{
    protected static string $resource = DepartmentReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
