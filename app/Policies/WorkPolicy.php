<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Work;

class WorkPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author', 'editor']);
    }

    public function view(User $user, Work $work): bool
    {
        return $user->id === $work->user_id || $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']);
    }

    public function update(User $user, Work $work): bool
    {
        return $user->id === $work->user_id || $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, Work $work): bool
    {
        return $user->id === $work->user_id || $user->hasRole('admin');
    }
}
