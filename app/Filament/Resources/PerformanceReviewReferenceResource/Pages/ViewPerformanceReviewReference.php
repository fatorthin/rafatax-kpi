<?php

namespace App\Filament\Resources\PerformanceReviewReferenceResource\Pages;

use App\Filament\Resources\PerformanceReviewReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPerformanceReviewReference extends ViewRecord
{
    protected static string $resource = PerformanceReviewReferenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
