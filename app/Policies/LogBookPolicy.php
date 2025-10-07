<?php

namespace App\Policies;

use App\Models\LogBook;
use App\Models\User;

class LogBookPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('logbook.viewAny');
    }

    public function view(User $user, LogBook $model): bool
    {
        return $user->can('logbook.view');
    }

    public function create(User $user): bool
    {
        return $user->can('logbook.create');
    }

    public function update(User $user, LogBook $model): bool
    {
        return $user->can('logbook.update');
    }

    public function delete(User $user, LogBook $model): bool
    {
        return $user->can('logbook.delete');
    }
}


