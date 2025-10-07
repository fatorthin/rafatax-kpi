<?php

namespace App\Filament\Resources\PerformanceStaffResource\Pages;

use App\Filament\Resources\PerformanceStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPerformanceStaff extends ViewRecord
{
    protected static string $resource = PerformanceStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
