<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesAuthorOwnedRecords
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user || $user->canViewAllAuthorData()) {
            return $query;
        }

        if (static::$authorOwnershipPath === '@user_id') {
            return $query->where('user_id', $user->getKey());
        }

        return $query->whereHas(
            static::$authorOwnershipPath,
            fn (Builder $ownerQuery): Builder => $ownerQuery->where('user_id', $user->getKey()),
        );
    }
}
