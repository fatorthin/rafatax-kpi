<?php

namespace App\Policies;

use App\Models\ClientReport;
use App\Models\User;

class ClientReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clientreport.viewAny');
    }

    public function view(User $user, ClientReport $model): bool
    {
        return $user->can('clientreport.view');
    }

    public function create(User $user): bool
    {
        return $user->can('clientreport.create');
    }

    public function update(User $user, ClientReport $model): bool
    {
        // Block editing verified records unless user can verify (e.g., admin)
        if ($model->is_verified) {
            return $user->can('clientreport.verify');
        }
        return $user->can('clientreport.update');
    }

    public function delete(User $user, ClientReport $model): bool
    {
        // Admins or roles with permission can always delete
        if ($user->can('clientreport.delete')) {
            return true;
        }

        // Staff can delete their own records only if not verified
        if (!$model->is_verified && $user->staff_id && $user->staff_id === $model->staff_id) {
            return true;
        }

        return false;
    }

    public function verify(User $user, ClientReport $model): bool
    {
        return $user->can('clientreport.verify');
    }
}


