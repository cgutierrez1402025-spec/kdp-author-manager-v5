<?php

namespace App\Jobs;

use App\Models\AiMessage;
use App\Services\Ollama\OllamaClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessOllamaChat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 900;

    public function __construct(public AiMessage $assistantMessage) {}

    public function handle(OllamaClient $client): void
    {
        $assistantMessage = $this->assistantMessage->fresh(['conversation.messages']);
        $conversation = $assistantMessage->conversation;

        $assistantMessage->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $messages = $conversation->messages
                ->where('sequence', '<', $assistantMessage->sequence)
                ->whereIn('role', ['user', 'assistant'])
                ->map(fn (AiMessage $message): array => [
                    'role' => $message->role,
                    'content' => $message->content ?? '',
                ])
                ->values()
                ->all();

            $options = $conversation->messages
                ->where('role', 'user')
                ->last()
                ?->metadata['ollama_options'] ?? [];

            $response = $client->chat(
                $messages,
                $conversation->model,
                $options,
                $conversation->system_prompt,
            );

            $content = trim((string) data_get($response, 'message.content', ''));

            if ($content === '') {
                throw new \RuntimeException('Ollama ha devuelto una respuesta vacía.');
            }

            $assistantMessage->update([
                'content' => $content,
                'thinking_content' => data_get($response, 'message.thinking'),
                'status' => 'completed',
                'metadata' => $response,
                'completed_at' => now(),
            ]);

            $conversation->update([
                'status' => 'active',
                'last_message_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Ollama chat failed', [
                'message_id' => $assistantMessage->id,
                'error' => $exception->getMessage(),
            ]);

            $assistantMessage->update([
                'status' => 'failed',
                'content' => 'No se pudo completar la petición: '.$exception->getMessage(),
                'metadata' => ['error' => $exception->getMessage()],
                'completed_at' => now(),
            ]);

            $conversation->update(['status' => 'error']);

            throw $exception;
        }
    }
}