<?php

namespace App\Services;

use App\Models\RoyaltyEntry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoyaltyImportService
{
    public function import(array $rows): int
    {
        return DB::transaction(function () use ($rows): int {
            $processed = 0;

            foreach ($rows as $index => $row) {
                $data = Validator::make($row, [
                    'publication_id' => ['required', 'integer', 'exists:publications,id'],
                    'year' => ['required', 'integer', 'between:2000,2100'],
                    'month' => ['required', 'integer', 'between:1,12'],
                    'paid_units' => ['required', 'integer', 'min:0'],
                    'free_units' => ['required', 'integer', 'min:0'],
                    'kenp_pages' => ['required', 'integer', 'min:0'],
                    'royalty_ebook' => ['required', 'numeric', 'min:0'],
                    'royalty_paperback' => ['required', 'numeric', 'min:0'],
                    'royalty_hardcover' => ['required', 'numeric', 'min:0'],
                    'royalty_kenp' => ['required', 'numeric', 'min:0'],
                    'total_royalty' => ['required', 'numeric', 'min:0'],
                    'currency' => ['required', 'string', 'size:3'],
                    'source_file' => ['nullable', 'string', 'max:512'],
                    'notes' => ['nullable', 'string'],
                ])->validate();

                $componentTotal = round(
                    $data['royalty_ebook']
                    + $data['royalty_paperback']
                    + $data['royalty_hardcover']
                    + $data['royalty_kenp'],
                    2,
                );

                if (abs($componentTotal - round((float) $data['total_royalty'], 2)) > 0.01) {
                    throw ValidationException::withMessages([
                        "rows.{$index}.total_royalty" => 'El total no coincide con la suma de los componentes.',
                    ]);
                }

                $data['currency'] = strtoupper($data['currency']);

                RoyaltyEntry::updateOrCreate(
                    [
                        'publication_id' => $data['publication_id'],
                        'year' => $data['year'],
                        'month' => $data['month'],
                    ],
                    $data,
                );

                $processed++;
            }

            return $processed;
        });
    }
}
