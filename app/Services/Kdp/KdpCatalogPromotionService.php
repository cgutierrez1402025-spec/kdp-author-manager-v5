<?php

namespace App\Services\Kdp;

use App\Models\KdpCatalogItem;
use App\Models\KdpMetadata;
use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\Publication;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkLanguage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class KdpCatalogPromotionService
{
    public function createWork(KdpCatalogItem $item, array $data, User $user): Work
    {
        $this->authorize($item, $user);

        return DB::transaction(function () use ($item, $data): Work {
            $title = trim((string) ($data['title'] ?? $item->title));
            $author = trim((string) ($data['author_name'] ?? $item->author));
            if ($title === '' || $author === '') {
                throw ValidationException::withMessages(['title' => 'Título y autor son obligatorios.']);
            }

            $work = Work::create([
                'user_id' => $item->user_id, 'title' => $title, 'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
                'title_internal' => $title, 'title_public' => $title, 'author_name' => $author,
                'original_language' => $data['language_code'], 'status' => 'catalog_review',
                'work_type' => $data['work_type'] ?? null,
                'notes' => 'Creada desde catálogo KDP detectado #'.$item->id.'. Completar los datos editoriales y asociar el manuscrito real.',
            ]);
            $language = WorkLanguage::create([
                'work_id' => $work->id, 'language_code' => $data['language_code'],
                'translation_status' => 'original', 'ai_translation_used' => false,
            ]);
            $this->link($item, $work, $language, $data);

            return $work->refresh();
        });
    }

    public function linkExisting(KdpCatalogItem $item, array $data, User $user): Work
    {
        $this->authorize($item, $user);
        $work = Work::whereKey($data['work_id'])->where('user_id', $item->user_id)->firstOrFail();
        $language = WorkLanguage::whereKey($data['work_language_id'])->where('work_id', $work->id)->firstOrFail();

        DB::transaction(fn () => $this->link($item, $work, $language, $data));

        return $work;
    }

    private function link(KdpCatalogItem $item, Work $work, WorkLanguage $language, array $data): Publication
    {
        $platform = Platform::firstOrCreate(['name' => 'Amazon KDP'], ['description' => 'Kindle Direct Publishing']);
        $marketplace = Marketplace::whereKey($data['marketplace_id'])->where('platform_id', $platform->id)->first();
        if (! $marketplace) {
            throw ValidationException::withMessages(['marketplace_id' => 'Selecciona un marketplace de Amazon KDP.']);
        }

        $manuscriptId = $data['manuscript_version_id'] ?? null;
        if ($manuscriptId && ! ManuscriptVersion::whereKey($manuscriptId)->where('work_id', $work->id)->where('work_language_id', $language->id)->where('is_final', true)->exists()) {
            throw ValidationException::withMessages(['manuscript_version_id' => 'El manuscrito debe ser una versión final de la obra y el idioma seleccionados.']);
        }

        $format = $data['format'] ?? $item->format;
        if (! in_array($format, ['ebook', 'paperback', 'hardcover', 'audiobook'], true)) {
            throw ValidationException::withMessages(['format' => 'Selecciona un formato válido.']);
        }

        $publication = Publication::query()
            ->when($item->asin, fn ($query) => $query->where('asin', $item->asin)->where('marketplace_id', $marketplace->id), fn ($query) => $query->where('external_identifier', 'kdp-catalog:'.$item->id))
            ->first();

        if ($publication && $publication->work_id !== $work->id) {
            throw ValidationException::withMessages(['work_id' => 'Ese ASIN ya pertenece a otra obra.']);
        }

        $publication ??= new Publication;
        $publication->fill([
            'work_id' => $work->id, 'work_language_id' => $language->id, 'manuscript_version_id' => $manuscriptId,
            'platform_id' => $platform->id, 'marketplace_id' => $marketplace->id, 'format' => $format,
            'external_identifier' => 'kdp-catalog:'.$item->id, 'status' => 'catalog_review',
            'asin' => $item->asin, 'isbn' => $item->isbn, 'currency' => $marketplace->currency,
            'notes' => 'Edición comercial importada desde el catálogo KDP; pendiente de completar y verificar.',
        ])->save();

        KdpMetadata::updateOrCreate(['publication_id' => $publication->id], [
            'title' => $item->title ?: $work->title_public,
            'author' => $item->author ?: $work->author_name,
        ]);
        $item->reportRows()->update(['publication_id' => $publication->id]);
        $item->update(['work_id' => $work->id, 'publication_id' => $publication->id, 'review_status' => 'linked']);

        return $publication;
    }

    private function authorize(KdpCatalogItem $item, User $user): void
    {
        abort_unless($item->user_id === $user->id || $user->canViewAllAuthorData(), 403);
    }
}
