<?php

namespace Tests\Feature;

use App\Services\AiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiServiceTest extends TestCase
{
    public function test_it_fails_cleanly_without_an_api_key(): void
    {
        config(['services.openai.key' => null]);

        $result = app(AiService::class)->generateContent('Test');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('OPENAI_API_KEY', $result['error']);
    }

    public function test_it_returns_provider_content(): void
    {
        config(['services.openai.key' => 'test-key']);
        Http::fake([
            'api.openai.com/*' => Http::response([
                'id' => 'resp_test',
                'output' => [[
                    'type' => 'message',
                    'content' => [['type' => 'output_text', 'text' => 'Generated text']],
                ]],
                'usage' => ['input_tokens' => 2, 'output_tokens' => 3],
            ]),
        ]);

        $result = app(AiService::class)->generateContent('Test prompt');

        $this->assertTrue($result['success']);
        $this->assertSame('Generated text', $result['result']);
        $this->assertSame('resp_test', $result['response_id']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.openai.com/v1/responses'
            && $request['input'] === 'Test prompt'
            && $request->hasHeader('Authorization', 'Bearer test-key'));
    }
}
