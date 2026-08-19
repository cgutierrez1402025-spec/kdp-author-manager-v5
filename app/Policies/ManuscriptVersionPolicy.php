<?php

namespace App\Policies;

use App\Models\ManuscriptVersion;
use App\Models\User;

class ManuscriptVersionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author', 'editor']);
    }

    public function view(User $user, ManuscriptVersion $manuscriptVersion): bool
    {
        return $user->id === $manuscriptVersion->work->user_id || $user->hasAnyRole(['admin', 'editor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'author']);
    }

    public function update(User $user, ManuscriptVersion $manuscriptVersion): bool
    {
        return $user->id === $manuscriptVersion->work->user_id || $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, ManuscriptVersion $manuscriptVersion): bool
    {
        return $user->id === $manuscriptVersion->work->user_id || $user->hasRole('admin');
    }
}
