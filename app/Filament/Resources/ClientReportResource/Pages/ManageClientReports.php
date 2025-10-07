<?php

namespace App\Filament\Resources\ClientReportResource\Pages;

use App\Filament\Resources\ClientReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageClientReports extends ManageRecords
{
    protected static string $resource = ClientReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
