<?php

namespace App\Filament\Resources\PerformanceStaffResource\Pages;

use App\Filament\Resources\PerformanceStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerformanceStaff extends ListRecords
{
    protected static string $resource = PerformanceStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
