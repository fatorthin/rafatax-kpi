<?php

namespace App\Filament\Staff\Resources\CaseProjectResource\Pages;

use App\Filament\Staff\Resources\CaseProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManageCaseProjects extends ManageRecords
{
    protected static string $resource = CaseProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Di panel staff, auto-assign staff_id ke user yang login (admin/staff)
                    $user = Auth::user();
                    if ($user && $user->staff_id) {
                        $data['staff_id'] = $user->staff_id;
                    }
                    return $data;
                }),
        ];
    }
}
