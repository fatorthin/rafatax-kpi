<?php

namespace App\Filament\Resources\PositionReferenceResource\Pages;

use App\Filament\Resources\PositionReferenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPositionReference extends EditRecord
{
    protected static string $resource = PositionReferenceResource::class;

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
