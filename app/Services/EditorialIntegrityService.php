<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use Illuminate\Validation\ValidationException;

class EditorialIntegrityService
{
    public function validateManuscript(array $data, User $user): array
    {
        $work = Work::findOrFail($data['work_id']);
        $this->ensureWorkAccess($work, $user);

        $languageMatches = WorkLanguage::whereKey($data['work_language_id'])
            ->where('work_id', $work->id)
            ->exists();

        if (! $languageMatches) {
            throw ValidationException::withMessages([
                'work_language_id' => 'El idioma seleccionado no pertenece a la obra.',
            ]);
        }

        if (! empty($data['parent_version_id'])) {
            $parentMatches = ManuscriptVersion::whereKey($data['parent_version_id'])
                ->where('work_id', $work->id)
                ->where('work_language_id', $data['work_language_id'])
                ->exists();

            if (! $parentMatches) {
                throw ValidationException::withMessages([
                    'parent_version_id' => 'La versión padre no pertenece a la obra y al idioma seleccionados.',
                ]);
            }
        }

        if (! empty($data['edition_id'])) {
            $editionMatches = Edition::whereKey($data['edition_id'])
                ->where('work_id', $work->id)
                ->where('work_language_id', $data['work_language_id'])
                ->exists();

            if (! $editionMatches) {
                throw ValidationException::withMessages([
                    'edition_id' => 'La edición no pertenece a la obra y al idioma seleccionados.',
                ]);
            }
        }

        return $data;
    }

    public function validatePublication(array $data, User $user): array
    {
        $work = Work::findOrFail($data['work_id']);
        $this->ensureWorkAccess($work, $user);

        $languageMatches = WorkLanguage::whereKey($data['work_language_id'])
            ->where('work_id', $work->id)
            ->exists();

        if (! $languageMatches) {
            throw ValidationException::withMessages([
                'work_language_id' => 'El idioma seleccionado no pertenece a la obra.',
            ]);
        }

        $manuscriptMatches = ManuscriptVersion::whereKey($data['manuscript_version_id'])
            ->where('work_id', $work->id)
            ->where('work_language_id', $data['work_language_id'])
            ->where('is_final', true)
            ->exists();

        if (! $manuscriptMatches) {
            throw ValidationException::withMessages([
                'manuscript_version_id' => 'La publicación requiere un manuscrito final de la obra y el idioma seleccionados.',
            ]);
        }

        if (! empty($data['marketplace_id'])) {
            $marketplaceMatches = Marketplace::whereKey($data['marketplace_id'])
                ->where('platform_id', $data['platform_id'])
                ->exists();

            if (! $marketplaceMatches) {
                throw ValidationException::withMessages([
                    'marketplace_id' => 'El marketplace no pertenece a la plataforma seleccionada.',
                ]);
            }
        }

        return $data;
    }

    private function ensureWorkAccess(Work $work, User $user): void
    {
        if ($work->user_id !== $user->id && ! $user->hasAnyRole(['admin', 'editor'])) {
            throw ValidationException::withMessages([
                'work_id' => 'No tienes permiso para utilizar esta obra.',
            ]);
        }
    }
}
