<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Prompt extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'ai_tool_id',
        'task_id',
        'title',
        'prompt_text',
        'language_code',
        'purpose',
        'result_summary',
        'rating',
        'reused',
        'generated_final_content',
        'result_text',
    ];

    protected $casts = [
        'rating' => 'integer',
        'reused' => 'boolean',
        'generated_final_content' => 'boolean',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function aiTool(): BelongsTo
    {
        return $this->belongsTo(AiTool::class, 'ai_tool_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(AiTask::class, 'task_id');
    }

    public function execute(string $input = ''): array
    {
        $prompt = $this->buildFullPrompt($input);

        try {
            $response = $this->callAiProvider($prompt);

            $this->update([
                'result_text' => $response,
                'rating' => $this->rating ?? 5,
            ]);

            return [
                'success' => true,
                'result' => $response,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::error('AI prompt execution failed', [
                'prompt_id' => $this->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'result' => null,
            ];
        }
    }

    protected function buildFullPrompt(string $input): string
    {
        $parts = [$this->prompt_text];

        if ($input) {
            $parts[] = "\n\nInput:\n".$input;
        }

        if ($this->purpose) {
            $parts[] = "\n\nPurpose: ".$this->purpose;
        }

        return implode("\n", $parts);
    }

    protected function callAiProvider(string $prompt): string
    {
        $provider = $this->aiTool->provider;
        $model = $this->aiTool->model ?? $this->getDefaultModel($provider);

        return match ($provider) {
            'openai' => $this->callOpenAI($prompt, $model),
            'anthropic' => $this->callAnthropic($prompt, $model),
            'google' => $this->callGoogle($prompt, $model),
            'cohere' => $this->callCohere($prompt, $model),
            default => throw new \RuntimeException("Unsupported provider: {$provider}"),
        };
    }

    protected function getDefaultModel(string $provider): string
    {
        return match ($provider) {
            'openai' => config('services.openai.model', 'gpt-5.6-luna'),
            'anthropic' => 'claude-3-sonnet-20240229',
            'google' => 'gemini-pro',
            'cohere' => 'command-r',
            default => 'gpt-4',
        };
    }

    protected function callOpenAI(string $prompt, string $model): string
    {
        $response = app(AiService::class)->generateContent($prompt, $model);

        if (! $response['success']) {
            throw new \RuntimeException($response['error']);
        }

        return $response['result'];
    }

    protected function callAnthropic(string $prompt, string $model): string
    {
        $response = Http::withToken(config('services.anthropic.key'))
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => 2048,
            ]);

        return $response->json('content.0.text', '');
    }

    protected function callGoogle(string $prompt, string $model): string
    {
        $response = Http::withToken(config('services.google.key'))
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent', [
                'contents' => [['parts' => [['text' => $prompt]]]],
            ]);

        return $response->json('candidates.0.content.parts.0.text', '');
    }

    protected function callCohere(string $prompt, string $model): string
    {
        $response = Http::withToken(config('services.cohere.key'))
            ->post('https://api.cohere.ai/v1/generate', [
                'model' => $model,
                'prompt' => $prompt,
                'max_tokens' => 2048,
            ]);

        return $response->json('generations.0.text', '');
    }
}
