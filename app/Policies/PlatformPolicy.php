<?php

namespace App\Policies;

use App\Models\Platform;
use App\Models\User;

class PlatformPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_works');
    }

    public function view(User $user, Platform $platform): bool
    {
        return $user->can('view_works');
    }

    public function create(User $user): bool
    {
        return $user->can('create_works');
    }

    public function update(User $user, Platform $platform): bool
    {
        return $user->can('edit_works');
    }

    public function delete(User $user, Platform $platform): bool
    {
        return $user->can('delete_works');
    }
}
