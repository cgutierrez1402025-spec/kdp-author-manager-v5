<?php

namespace App\Services\Ollama;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class OllamaClient
{
    protected function request(): PendingRequest
    {
        return Http::baseUrl(rtrim(config('services.ollama.url'), '/'))
            ->acceptJson()
            ->timeout((int) config('services.ollama.timeout', 600));
    }

    public function version(): array
    {
        return $this->request()->get('/api/version')->throw()->json();
    }

    public function models(): array
    {
        return $this->request()->get('/api/tags')->throw()->json('models', []);
    }

    public function chat(array $messages, string $model, array $options = [], ?string $systemPrompt = null): array
    {
        $think = $options['think'] ?? false;
        unset($options['think']);

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
            'options' => $options,
            'think' => $think,
        ];

        if ($systemPrompt) {
            array_unshift($payload['messages'], [
                'role' => 'system',
                'content' => $systemPrompt,
            ]);
        }

        return $this->request()->post('/api/chat', $payload)->throw()->json();
    }
}