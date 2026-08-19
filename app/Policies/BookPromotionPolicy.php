<?php

namespace App\Policies;

use App\Models\BookPromotion;
use App\Models\User;

class BookPromotionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_works');
    }

    public function view(User $user, BookPromotion $bookPromotion): bool
    {
        return $user->can('view_works');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_promotions');
    }

    public function update(User $user, BookPromotion $bookPromotion): bool
    {
        return $user->can('manage_promotions');
    }

    public function delete(User $user, BookPromotion $bookPromotion): bool
    {
        return $user->can('manage_promotions');
    }
}
