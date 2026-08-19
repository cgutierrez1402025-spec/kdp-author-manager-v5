<?php

namespace Database\Factories;

use App\Models\ManuscriptVersion;
use App\Models\Marketplace;
use App\Models\Platform;
use App\Models\Publication;
use App\Models\Work;
use App\Models\WorkLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = Publication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_id' => Work::factory(),
            'work_language_id' => WorkLanguage::factory(),
            'manuscript_version_id' => ManuscriptVersion::factory(),
            'platform_id' => Platform::factory(),
            'marketplace_id' => Marketplace::factory(),
            'format' => $this->faker->randomElement(['ebook', 'paperback', 'hardcover', 'audiobook']),
            'external_identifier' => $this->faker->uuid(),
            'public_url' => $this->faker->url(),
            'status' => $this->faker->randomElement(['draft', 'processing', 'published', 'error']),
            'price' => $this->faker->randomFloat(2, 0.99, 99.99),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD']),
            'territories' => $this->faker->randomElement(['world', 'us', 'eu', 'uk', 'asia']),
            'isbn' => $this->faker->isbn13(),
            'asin' => $this->faker->bothify('??????????'), // 10 character alphanumeric
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that this is an ebook publication.
     */
    public function ebook(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'ebook',
        ]);
    }

    /**
     * Indicate that this is a paperback publication.
     */
    public function paperback(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'paperback',
        ]);
    }

    /**
     * Indicate that this is a hardcover publication.
     */
    public function hardcover(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'hardcover',
        ]);
    }

    /**
     * Indicate that this is an audiobook publication.
     */
    public function audiobook(): static
    {
        return $this->state(fn (array $attributes) => [
            'format' => 'audiobook',
        ]);
    }

    /**
     * Indicate that this publication is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    /**
     * Indicate that this publication is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that this publication has no ISBN/ASIN.
     */
    public function withoutIdentifiers(): static
    {
        return $this->state(fn (array $attributes) => [
            'isbn' => null,
            'asin' => null,
        ]);
    }

    /**
     * Indicate that this publication has a specific price.
     */
    public function withPrice(float $price): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
        ]);
    }
}
