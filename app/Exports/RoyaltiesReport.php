<?php

namespace App\Exports;

use App\Models\RoyaltyEntry;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RoyaltiesReport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    protected ?int $publicationId;

    protected ?int $platformId;

    protected ?string $startDate;

    protected ?string $endDate;

    public function __construct(?int $publicationId = null, ?int $platformId = null, ?string $startDate = null, ?string $endDate = null)
    {
        $this->publicationId = $publicationId;
        $this->platformId = $platformId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = RoyaltyEntry::with(['publication.work', 'publication.marketplace']);

        if ($this->publicationId) {
            $query->where('publication_id', $this->publicationId);
        }

        if ($this->platformId) {
            $query->whereHas('publication', fn ($q) => $q->where('platform_id', $this->platformId));
        }

        if ($this->startDate) {
            $query->where(function ($q) {
                $q->where('year', '>', substr($this->startDate, 0, 4))
                    ->orWhere(function ($sub) {
                        $sub->where('year', substr($this->startDate, 0, 4))
                            ->where('month', '>=', substr($this->startDate, 5, 2));
                    });
            });
        }

        return $query->orderBy('year')->orderBy('month')->get();
    }

    public function headings(): array
    {
        return [
            'Publicación',
            'Año',
            'Mes',
            'Unidades Pagadas',
            'Unidades Gratis',
            'Páginas KENP',
            'Royalties eBook',
            'Royalties Tapa Blanda',
            'Royalties Tapa Dura',
            'Royalties KENP',
            'Total',
            'Moneda',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->publication->work->title_public ?? 'N/A',
            $entry->year,
            $entry->month,
            $entry->paid_units,
            $entry->free_units,
            $entry->kenp_pages,
            $entry->royalty_ebook,
            $entry->royalty_paperback,
            $entry->royalty_hardcover,
            $entry->royalty_kenp,
            $entry->total_royalty,
            $entry->currency ?? 'USD',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
