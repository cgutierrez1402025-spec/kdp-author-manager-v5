<?php

namespace App\Services\Kdp;

use Carbon\Carbon;
use RuntimeException;

class KdpReportTypeDetector
{
    private const SIGNATURES = [
        'payments' => ['payment number', 'payment date', 'payment amount', 'payment status'],
        'kenp' => ['kenp read', 'kenp pages read', 'paginas kenp leidas'],
        'orders' => ['units ordered', 'unidades pedidas', 'orders'],
        'sales_royalties' => ['transaction type', 'royalty type', 'average offer price without tax'],
        'prior_royalties' => ['total earnings', 'net units sold', 'units refunded'],
        'historical' => ['royalty date', 'accrued royalty', 'historical'],
    ];

    public function detect(string $path): array
    {
        $tables = $this->tables($path);
        $haystack = collect($tables)->flatMap(fn (array $rows, string $sheet) => [$sheet, ...collect(array_slice($rows, 0, 25))->flatten()->all()])
            ->map(fn ($value) => $this->normalize((string) $value))->filter()->unique();

        $scores = collect(self::SIGNATURES)->map(function (array $terms) use ($haystack): int {
            return collect($terms)->filter(fn (string $term) => $haystack->contains(fn (string $value) => str_contains($value, $this->normalize($term))))->count();
        })->sortDesc();

        $type = $scores->keys()->first();
        $score = (int) $scores->first();
        $maximum = count(self::SIGNATURES[$type] ?? []);

        return [
            'type' => $score > 0 ? $type : null,
            'confidence' => $maximum ? round(($score / $maximum) * 100, 2) : 0,
            'period' => $this->period(basename($path)),
            'sheets' => array_keys($tables),
        ];
    }

    private function tables(string $path): array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === 'xlsx') {
            return app(XlsxTableReader::class)->read($path);
        }
        if (! in_array($extension, ['csv', 'txt'], true)) {
            throw new RuntimeException('Formato no admitido para detección.');
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('No se puede abrir el informe.');
        }
        $firstLine = (string) fgets($handle);
        rewind($handle);
        $delimiter = collect([',', ';', "\t"])->sortByDesc(fn (string $candidate) => substr_count($firstLine, $candidate))->first();
        $rows = [];
        while (($row = fgetcsv($handle, separator: $delimiter)) !== false && count($rows) < 25) {
            $rows[] = $row;
        }
        fclose($handle);

        return ['CSV' => $rows];
    }

    private function period(string $filename): ?string
    {
        if (preg_match('/(20\d{2})[-_. ]?(0[1-9]|1[0-2])/', $filename, $matches)) {
            return Carbon::create((int) $matches[1], (int) $matches[2], 1)->toDateString();
        }
        if (preg_match('/(0[1-9]|1[0-2])[-_. ]?(20\d{2})/', $filename, $matches)) {
            return Carbon::create((int) $matches[2], (int) $matches[1], 1)->toDateString();
        }

        return null;
    }

    private function normalize(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', mb_strtolower(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value))));
    }
}
