<?php

namespace App\Services\Kdp;

use App\Models\KdpCatalogItem;
use App\Models\KdpMetadata;
use App\Models\KdpReportRow;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\Publication;
use App\Models\Work;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KdpCatalogMaterializer
{
    public function materialize(KdpCatalogItem $item): Work
    {
        return DB::transaction(function () use ($item): Work {
            $item->refresh();
            $existingPublication = $item->asin ? Publication::query()->where('asin', $item->asin)
                ->whereHas('work', fn ($query) => $query->where('user_id', $item->user_id))->first() : null;
            $reportedTitle = trim((string) $item->title);
            $hasReportedTitle = $reportedTitle !== '' && $reportedTitle !== 'Título no informado';
            $editionIdentifier = $item->asin ?: $item->isbn ?: '#'.$item->id;
            $title = $hasReportedTitle ? $reportedTitle : 'Edición KDP '.$editionIdentifier;
            $identitySource = $this->normalize($title).'|'.$this->normalize((string) $item->author);
            $identity = hash('sha256', $identitySource);
            $work = $existingPublication?->work ?? Work::firstOrCreate(
                ['user_id' => $item->user_id, 'kdp_identity_key' => $identity],
                [
                    'title' => $title, 'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
                    'title_internal' => $title, 'title_public' => $title, 'author_name' => $item->author,
                    'original_language' => null, 'status' => 'catalog_review',
                    'notes' => 'Creada automáticamente desde informes KDP. Idioma, clasificación y manuscrito pendientes de revisión.',
                ],
            );

            $firstPublication = $existingPublication;
            $item->reportRows()->where('row_kind', '!=', 'payment')->each(function (KdpReportRow $row) use ($item, $work, &$firstPublication): void {
                $marketplace = $this->marketplace($row->marketplace, $row->currency);
                $publication = $this->publication($item, $work, $row, $marketplace);
                $row->update(['publication_id' => $publication->id]);
                $firstPublication ??= $publication;
            });

            if (! $firstPublication) {
                $firstPublication = $this->publication($item, $work, null, null);
            }

            $item->update([
                'work_id' => $work->id, 'publication_id' => $firstPublication->id,
                'review_status' => 'linked',
            ]);

            return $work;
        });
    }

    public function materializeAll(?int $userId = null): int
    {
        $count = 0;
        KdpCatalogItem::query()->when($userId, fn ($query) => $query->where('user_id', $userId))
            ->chunkById(100, function ($items) use (&$count): void {
                foreach ($items as $item) {
                    $this->materialize($item);
                    $count++;
                }
            });

        return $count;
    }

    private function publication(KdpCatalogItem $item, Work $work, ?KdpReportRow $row, ?Marketplace $marketplace): Publication
    {
        $platform = Platform::firstOrCreate(['name' => 'Amazon KDP'], ['description' => 'Kindle Direct Publishing']);
        $format = $row?->format ?: $item->format ?: 'unknown';
        $publication = $row?->publication_id
            ? Publication::whereKey($row->publication_id)->where('work_id', $work->id)->first()
            : null;
        $publication ??= Publication::query()
            ->when($item->asin, fn ($query) => $query->where('asin', $item->asin)->when($marketplace, fn ($q) => $q->where('marketplace_id', $marketplace->id), fn ($q) => $q->whereNull('marketplace_id')),
                fn ($query) => $query->where('external_identifier', 'kdp-catalog:'.$item->id.':'.($marketplace?->id ?? 'none')))
            ->first();

        if (! $publication) {
            $publication = Publication::create([
                'work_id' => $work->id, 'work_language_id' => null, 'manuscript_version_id' => null,
                'platform_id' => $platform->id, 'marketplace_id' => $marketplace?->id, 'format' => $format,
                'external_identifier' => 'kdp-catalog:'.$item->id.':'.($marketplace?->id ?? 'none'),
                'status' => 'catalog_review', 'asin' => $item->asin, 'isbn' => $item->isbn,
                'currency' => $row?->currency ?: $marketplace?->currency,
                'notes' => 'Creada automáticamente desde informe KDP; completar idioma y manuscrito.',
            ]);
        }

        KdpMetadata::updateOrCreate(['publication_id' => $publication->id], [
            'title' => $item->title ?: $work->title_public, 'author' => $item->author,
        ]);

        return $publication;
    }

    private function marketplace(?string $name, ?string $currency): ?Marketplace
    {
        if (! $name) {
            return null;
        }
        $platform = Platform::firstOrCreate(['name' => 'Amazon KDP'], ['description' => 'Kindle Direct Publishing']);
        $normalized = mb_strtolower(trim($name));
        $marketplace = Marketplace::where('platform_id', $platform->id)
            ->where(fn ($query) => $query->whereRaw('LOWER(code) = ?', [$normalized])->orWhereRaw('LOWER(name) = ?', [$normalized]))->first();

        return $marketplace ?? Marketplace::create([
            'platform_id' => $platform->id, 'code' => Str::limit(Str::slug($normalized, '.'), 100, ''),
            'name' => trim($name), 'currency' => $currency,
        ]);
    }

    private function normalize(string $value): string
    {
        return (string) Str::of($value)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish();
    }
}
