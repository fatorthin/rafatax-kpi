<?php

namespace App\Filament\Resources\PeriodPerformanceReviewResource\Pages;

use App\Filament\Resources\PeriodPerformanceReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePeriodPerformanceReview extends CreateRecord
{
    protected static string $resource = PeriodPerformanceReviewResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }  
}
