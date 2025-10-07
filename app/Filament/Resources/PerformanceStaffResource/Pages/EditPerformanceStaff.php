<?php

namespace App\Filament\Resources\PerformanceStaffResource\Pages;

use App\Filament\Resources\PerformanceStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerformanceStaff extends EditRecord
{
    protected static string $resource = PerformanceStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }  
}
