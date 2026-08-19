<?php

namespace App\Services\Audiobooks;

use App\Models\AudiobookEdition;
use Illuminate\Validation\ValidationException;

class AudiobookWorkflowService
{
    private const TRANSITIONS = [
        'idea' => ['rights_review', 'cancelled'],
        'rights_review' => ['production', 'cancelled'],
        'production' => ['quality_review', 'cancelled'],
        'quality_review' => ['ready', 'production'],
        'ready' => ['published', 'production'],
        'published' => ['withdrawn'],
        'withdrawn' => ['published'],
        'cancelled' => [],
    ];

    public function transition(AudiobookEdition $edition, string $status): AudiobookEdition
    {
        if (! in_array($status, self::TRANSITIONS[$edition->status] ?? [], true)) {
            throw ValidationException::withMessages(['status' => "No se puede pasar de {$edition->status} a {$status}."]);
        }

        if ($status === 'published') {
            $this->validateForPublication($edition);
        }

        $edition->update(['status' => $status, 'published_at' => $status === 'published' ? now() : $edition->published_at]);

        return $edition->refresh();
    }

    public function validateForPublication(AudiobookEdition $edition): void
    {
        $edition->loadMissing(['chapters', 'assets', 'qualityChecks', 'narrators']);
        $errors = [];

        if ($edition->rights_status !== 'confirmed') {
            $errors['rights_status'] = 'Los derechos de audio deben estar confirmados.';
        }
        if ($edition->chapters->isEmpty() || $edition->chapters->contains(fn ($chapter) => $chapter->status !== 'approved')) {
            $errors['chapters'] = 'Todos los capítulos deben estar aprobados.';
        }
        if ($edition->assets->isEmpty() || $edition->assets->contains(fn ($asset) => $asset->qa_status !== 'passed')) {
            $errors['assets'] = 'Los archivos de audio deben superar el control de calidad.';
        }
        if ($edition->qualityChecks->contains(fn ($check) => $check->status === 'failed')) {
            $errors['quality_checks'] = 'Hay controles de calidad fallidos.';
        }
        if ($edition->production_method === 'voice_replica' && $edition->narrators->contains(fn ($narrator) => ! $narrator->voice_consent || ($narrator->consent_expires_at && $narrator->consent_expires_at->isPast()))) {
            $errors['narrators'] = 'La réplica de voz exige consentimiento vigente de cada narrador.';
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
