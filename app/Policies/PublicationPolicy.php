<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;

class PublicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author', 'editor', 'accountant']);
    }

    public function view(User $user, Publication $publication): bool
    {
        return $user->id === $publication->work->user_id
            || $user->hasAnyRole(['admin', 'editor', 'accountant']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']);
    }

    public function update(User $user, Publication $publication): bool
    {
        return $user->id === $publication->work->user_id || $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, Publication $publication): bool
    {
        return $user->id === $publication->work->user_id || $user->hasRole('admin');
    }
}
