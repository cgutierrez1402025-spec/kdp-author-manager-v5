<?php

namespace App\Services;

use App\Models\Prompt;
use App\Models\Work;
use Illuminate\Support\Facades\Http;

class AiService
{
    protected ?string $apiKey;

    protected string $defaultModel;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->defaultModel = config('services.openai.model', 'gpt-5.6-luna');
    }

    public function generateContent(string $prompt, ?string $model = null): array
    {
        if (! $this->apiKey) {
            return ['success' => false, 'result' => null, 'error' => 'OPENAI_API_KEY is not configured.'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->acceptJson()
                ->timeout(60)
                ->retry(2, 250)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => $model ?: $this->defaultModel,
                    'input' => $prompt,
                ]);

            $response->throw();

            $result = collect($response->json('output', []))
                ->flatMap(fn (array $item): array => $item['content'] ?? [])
                ->firstWhere('type', 'output_text')['text'] ?? null;
            if (! is_string($result) || trim($result) === '') {
                throw new \RuntimeException('The AI provider returned an empty response.');
            }

            return [
                'success' => true,
                'result' => $result,
                'error' => null,
                'response_id' => $response->json('id'),
                'usage' => $response->json('usage'),
            ];
        } catch (\Throwable $exception) {
            return ['success' => false, 'result' => null, 'error' => $exception->getMessage()];
        }
    }

    public function suggestTags(string $workTitle, string $description): array
    {
        $prompt = "Suggest 5-10 relevant tags for a book with title '{$workTitle}' and description: {$description}. Return only comma-separated tags.";

        return $this->generateContent($prompt);
    }

    public function improveDescription(string $originalText): array
    {
        $prompt = "Improve this book description to make it more engaging and marketable. Keep it concise and compelling:\n\n{$originalText}";

        return $this->generateContent($prompt);
    }

    public function translateText(string $text, string $targetLanguage): array
    {
        $prompt = "Translate the following text to {$targetLanguage}:\n\n{$text}";

        return $this->generateContent($prompt);
    }

    public function savePromptExecution(
        Work $work,
        string $promptText,
        string $result,
        string $purpose,
        ?int $aiToolId = null,
        ?string $model = null,
        int $rating = 5
    ): Prompt {
        return Prompt::create([
            'work_id' => $work->id,
            'ai_tool_id' => $aiToolId,
            'title' => $this->getPurposeTitle($purpose),
            'prompt_text' => $promptText,
            'purpose' => $purpose,
            'result_text' => $result,
            'rating' => $rating,
            'reused' => false,
            'generated_final_content' => true,
        ]);
    }

    protected function getPurposeTitle(string $purpose): string
    {
        return match ($purpose) {
            'tags' => 'Tag Suggestions',
            'improve_description' => 'Description Improvement',
            'translate' => 'Translation',
            default => 'AI Generation',
        };
    }
}
