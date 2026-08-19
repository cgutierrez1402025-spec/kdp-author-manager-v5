<?php

namespace App\Services;

use App\Models\Publication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KdpApiService
{
    protected ?string $accessKey;

    protected ?string $secretKey;

    protected ?string $partnerTag;

    protected bool $demoMode;

    protected string $endpoint = 'https://webservices.amazon.com/paapi5';

    public function __construct()
    {
        $this->accessKey = config('services.amazon_paapi.access_key');
        $this->secretKey = config('services.amazon_paapi.secret_key');
        $this->partnerTag = config('services.amazon_paapi.partner_tag');
        $this->demoMode = (bool) config('services.amazon_paapi.demo_mode', false);
    }

    public function lookupByAsin(string $asin): array
    {
        if ($this->demoMode) {
            return $this->mockLookupByAsin($asin);
        }

        if (! $this->hasCredentials()) {
            return $this->configurationError();
        }

        try {
            return $this->realLookupByAsin($asin);
        } catch (\Throwable $e) {
            Log::error('Amazon API lookup failed', ['asin' => $asin, 'error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function realLookupByAsin(string $asin): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->generateAuthHeader(),
            'Content-Type' => 'application/json',
        ])
            ->post($this->endpoint.'/getitems', [
                'ItemIds' => [$asin],
                'Resources' => [
                    'ItemInfo.Title',
                    'Offers.Listings.Price',
                    'BrowseNodeInfo.BrowseNodes',
                ],
                'PartnerTag' => $this->partnerTag,
                'PartnerType' => 'Associates',
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to lookup ASIN: '.$response->body());
        }

        $data = $response->json();

        return [
            'success' => true,
            'title' => $data['Items'][0]['ItemInfo']['Title'] ?? null,
            'price' => $data['Items'][0]['Offers']['Listings'][0]['Price']['DisplayPrice'] ?? null,
            'ranking' => $data['Items'][0]['BrowseNodeInfo']['BrowseNodes'][0]['DisplayRank'] ?? null,
            'raw' => $data,
        ];
    }

    protected function mockLookupByAsin(string $asin): array
    {
        $cacheKey = "kdp_mock_asin_{$asin}";

        return Cache::remember($cacheKey, 3600, function () use ($asin) {
            $mockTitles = [
                'B00K3OM3PS' => 'The Silent Patient',
                'B0792J1FZ2' => 'Where the Crawdads Sing',
                'B07Q5YBRB8' => 'The Testaments',
                'B07P5ZDQHZ' => 'Normal People',
            ];

            $title = $mockTitles[$asin] ?? "Book Title for {$asin}";

            return [
                'success' => true,
                'title' => $title,
                'price' => rand(2, 15).'.99',
                'ranking' => rand(1000, 50000),
                'raw' => [],
            ];
        });
    }

    public function updateMetadata(int $publicationId): array
    {
        $publication = Publication::find($publicationId);

        if (! $publication || ! $publication->asin) {
            return [
                'success' => false,
                'error' => 'Publication or ASIN not found',
            ];
        }

        if ($this->demoMode) {
            return $this->mockUpdateMetadata($publication);
        }

        if (! $this->hasCredentials()) {
            return $this->configurationError();
        }

        try {
            return $this->realUpdateMetadata($publication);
        } catch (\Throwable $e) {
            Log::error('Amazon API update failed', ['publication_id' => $publicationId, 'error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function realUpdateMetadata(Publication $publication): array
    {
        $metadata = $publication->kdpMetadata;
        if (! $metadata) {
            return ['success' => false, 'error' => 'No KDP metadata found'];
        }

        $payload = [
            'asin' => $publication->asin,
            'title' => $metadata->title,
            'subtitle' => $metadata->subtitle,
            'description' => $metadata->description,
            'keywords' => $metadata->keywords,
            'author' => $metadata->author,
            'categories' => $metadata->categories,
            'age_range' => $metadata->age_range,
            'rights' => $metadata->rights,
            'ai_declaration' => $metadata->ai_declaration,
        ];

        $response = Http::withHeaders([
            'Authorization' => $this->generateAuthHeader(),
            'Content-Type' => 'application/json',
        ])
            ->put($this->endpoint.'/metadata/'.$publication->asin, $payload);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->body(),
            ];
        }

        $publication->update([
            'status' => 'processing',
            'external_identifier' => $publication->asin,
        ]);

        return [
            'success' => true,
            'message' => 'Metadata update submitted',
            'response' => $response->json(),
        ];
    }

    protected function mockUpdateMetadata(Publication $publication): array
    {
        $publication->update([
            'status' => 'processing',
            'external_identifier' => $publication->asin,
        ]);

        return [
            'success' => true,
            'message' => 'Metadata update submitted (mock)',
        ];
    }

    public function getSalesReport(string $startDate, string $endDate): array
    {
        if ($this->demoMode) {
            return $this->mockSalesReport($startDate, $endDate);
        }

        if (! $this->hasCredentials()) {
            return $this->configurationError();
        }

        try {
            return $this->realSalesReport($startDate, $endDate);
        } catch (\Throwable $e) {
            Log::error('Amazon API sales report failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function realSalesReport(string $startDate, string $endDate): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->generateAuthHeader(),
            'Content-Type' => 'application/json',
        ])
            ->get($this->endpoint.'/reports/sales', [
                'StartDate' => $startDate,
                'EndDate' => $endDate,
            ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'error' => $response->body(),
            ];
        }

        return [
            'success' => true,
            'data' => $response->json(),
        ];
    }

    protected function mockSalesReport(string $startDate, string $endDate): array
    {
        $cacheKey = "kdp_mock_sales_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, 300, function () {
            return [
                'success' => true,
                'data' => [
                    'total_sales' => rand(100, 1000),
                    'total_revenue' => rand(200, 2000).'.00',
                    'royalties_estimate' => rand(100, 1500).'.00',
                    'period_sales' => collect(range(1, 30))->map(fn ($day) => [
                        'date' => now()->subDays(30 - $day)->format('Y-m-d'),
                        'units' => rand(0, 50),
                        'revenue' => rand(10, 100).'.00',
                    ])->toArray(),
                ],
            ];
        });
    }

    protected function generateAuthHeader(): string
    {
        $date = gmdate('D, d M Y H:i:s T');
        $signature = base64_encode(hash_hmac('sha256', $date, $this->secretKey ?? ''));

        return "AWS4-HMAC-SHA256 Credential={$this->accessKey}, SignedHeaders=host;x-amz-date, Signature={$signature}";
    }

    protected function hasCredentials(): bool
    {
        return filled($this->accessKey) && filled($this->secretKey) && filled($this->partnerTag);
    }

    protected function configurationError(): array
    {
        return [
            'success' => false,
            'error' => 'Amazon API credentials are incomplete. Configure credentials or enable KDP_DEMO_MODE explicitly.',
        ];
    }

    public function syncPublication(Publication $publication): array
    {
        if (! $publication->asin) {
            return [
                'success' => false,
                'error' => 'No ASIN configured',
            ];
        }

        $result = $this->lookupByAsin($publication->asin);

        if ($result['success']) {
            $updateData = [];

            if ($publication->kdpMetadata) {
                $publication->kdpMetadata()->update([
                    'title' => $result['title'],
                ]);
            }

            $publication->update([
                'external_identifier' => $publication->asin,
            ]);

            return [
                'success' => true,
                'data' => $result,
                'message' => 'Publication synced successfully',
            ];
        }

        return $result;
    }
}
