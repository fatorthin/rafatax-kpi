<?php

namespace App\Filament\Resources\PerformanceStaffResource\Pages;

use App\Filament\Resources\PerformanceStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePerformanceStaff extends CreateRecord
{
    protected static string $resource = PerformanceStaffResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }  
}
