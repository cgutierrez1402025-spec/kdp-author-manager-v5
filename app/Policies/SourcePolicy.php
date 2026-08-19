<?php

namespace App\Policies;

use App\Models\Source;
use App\Models\User;

class SourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_works');
    }

    public function view(User $user, Source $source): bool
    {
        return $user->can('view_works');
    }

    public function create(User $user): bool
    {
        return $user->can('create_works');
    }

    public function update(User $user, Source $source): bool
    {
        return $user->can('edit_works');
    }

    public function delete(User $user, Source $source): bool
    {
        return $user->can('delete_works');
    }
}
