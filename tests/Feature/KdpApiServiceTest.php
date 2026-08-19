<?php

namespace Tests\Feature;

use App\Services\KdpApiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KdpApiServiceTest extends TestCase
{
    public function test_it_requires_credentials_when_demo_mode_is_disabled(): void
    {
        config([
            'services.amazon_paapi.demo_mode' => false,
            'services.amazon_paapi.access_key' => null,
            'services.amazon_paapi.secret_key' => null,
            'services.amazon_paapi.partner_tag' => null,
        ]);

        $result = app(KdpApiService::class)->lookupByAsin('B000TEST00');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('KDP_DEMO_MODE', $result['error']);
        Http::assertNothingSent();
    }

    public function test_demo_mode_must_be_enabled_explicitly(): void
    {
        config([
            'services.amazon_paapi.demo_mode' => true,
            'services.amazon_paapi.access_key' => null,
            'services.amazon_paapi.secret_key' => null,
            'services.amazon_paapi.partner_tag' => null,
        ]);

        $result = app(KdpApiService::class)->lookupByAsin('B000TEST00');

        $this->assertTrue($result['success']);
        $this->assertSame('Book Title for B000TEST00', $result['title']);
        Http::assertNothingSent();
    }

    public function test_provider_failures_are_not_replaced_with_mock_successes(): void
    {
        config([
            'services.amazon_paapi.demo_mode' => false,
            'services.amazon_paapi.access_key' => 'access',
            'services.amazon_paapi.secret_key' => 'secret',
            'services.amazon_paapi.partner_tag' => 'partner',
        ]);
        Http::fake(['webservices.amazon.com/*' => Http::response('Unavailable', 503)]);

        $result = app(KdpApiService::class)->lookupByAsin('B000TEST00');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Failed to lookup ASIN', $result['error']);
    }
}
