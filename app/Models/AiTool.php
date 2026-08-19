<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenAI\Client;

class AiTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'provider',
        'tool_type',
        'model',
        'url',
        'strengths',
        'weaknesses',
        'quality_score',
        'notes',
    ];

    protected $casts = [
        'quality_score' => 'integer',
    ];

    const PROVIDERS = [
        'openai' => 'OpenAI',
        'anthropic' => 'Anthropic',
        'google' => 'Google',
        'cohere' => 'Cohere',
    ];

    public function prompts(): HasMany
    {
        return $this->hasMany(Prompt::class);
    }

    public function getApiClient(): ?object
    {
        $apiKey = config("services.{$this->provider}.key");

        if (! $apiKey) {
            return null;
        }

        return match ($this->provider) {
            'openai' => new Client($apiKey),
            'anthropic' => new \Anthropic\Client($apiKey),
            'google' => new \Google\Client(['api_key' => $apiKey]),
            'cohere' => new \Cohere\Client($apiKey),
            default => null,
        };
    }
}
