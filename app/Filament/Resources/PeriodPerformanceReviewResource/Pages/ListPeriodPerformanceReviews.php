<?php

namespace App\Filament\Resources\PeriodPerformanceReviewResource\Pages;

use App\Filament\Resources\PeriodPerformanceReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPeriodPerformanceReviews extends ListRecords
{
    protected static string $resource = PeriodPerformanceReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
