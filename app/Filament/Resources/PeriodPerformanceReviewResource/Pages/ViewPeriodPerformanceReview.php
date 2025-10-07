<?php

namespace App\Filament\Resources\PeriodPerformanceReviewResource\Pages;

use App\Filament\Resources\PeriodPerformanceReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPeriodPerformanceReview extends ViewRecord
{
    protected static string $resource = PeriodPerformanceReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
