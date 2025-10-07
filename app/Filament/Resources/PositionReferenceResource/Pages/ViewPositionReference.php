<?php

namespace App\Filament\Resources\PositionReferenceResource\Pages;

use App\Filament\Resources\PositionReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPositionReference extends ViewRecord
{
    protected static string $resource = PositionReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
