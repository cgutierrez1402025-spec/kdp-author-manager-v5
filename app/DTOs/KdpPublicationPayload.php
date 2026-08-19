<?php

namespace App\DTOs;

use App\Models\Work;
use Illuminate\Support\Str;

readonly class KdpPublicationPayload
{
    public function __construct(public string $title, public string $description, public string $language, public array $categories, public string $format, public array $pricing, public ?string $manuscriptPath = null, public ?string $coverPath = null) {}

    public static function fromWorkAndData(Work $work, array $data): self
    {
        return new self(trim($work->title_public), Str::limit(trim($work->description ?? ''), 4000), strtolower($work->original_language), is_array($work->categories) ? $work->categories : [], $data['format'], ['currency' => strtoupper($data['currency'] ?? 'USD'), 'amount' => isset($data['price']) ? (float) $data['price'] : 0.00], $data['manuscript_path'] ?? null, $data['cover_path'] ?? null);
    }

    public function toArray(): array
    {
        return ['title' => $this->title, 'description' => $this->description, 'language' => $this->language, 'categories' => $this->categories, 'format' => $this->format, 'pricing' => $this->pricing, 'files' => array_filter(['manuscript' => $this->manuscriptPath, 'cover' => $this->coverPath])];
    }
}
