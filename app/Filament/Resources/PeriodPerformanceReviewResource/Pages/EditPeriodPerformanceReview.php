<?php

namespace App\Filament\Resources\PeriodPerformanceReviewResource\Pages;

use App\Filament\Resources\PeriodPerformanceReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPeriodPerformanceReview extends EditRecord
{
    protected static string $resource = PeriodPerformanceReviewResource::class;

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
