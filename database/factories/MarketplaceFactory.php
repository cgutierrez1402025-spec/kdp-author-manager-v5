<?php

namespace Database\Factories;

use App\Models\Marketplace;
use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Marketplace>
 */
class MarketplaceFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = Marketplace::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = Platform::factory()->create(); // Create a platform to associate with

        return [
            'platform_id' => $platform->id,
            'code' => $this->faker->regexify('[a-z]{2,5}_[a-z]{2}'), // Format like amazon_com
            'name' => $this->faker->company(),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'MXN', 'BRL']),
        ];
    }

    /**
     * Indicate that this is an Amazon US marketplace.
     */
    public function amazonUs(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_id' => Platform::where('name', 'Amazon KDP')->first()->id ?? Platform::factory()->kdp()->create()->id,
            'code' => 'amazon_us',
            'name' => 'Amazon.com',
            'currency' => 'USD',
        ]);
    }

    /**
     * Indicate that this is an Amazon UK marketplace.
     */
    public function amazonUk(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_id' => Platform::where('name', 'Amazon KDP')->first()->id ?? Platform::factory()->kdp()->create()->id,
            'code' => 'amazon_uk',
            'name' => 'Amazon.co.uk',
            'currency' => 'GBP',
        ]);
    }

    /**
     * Indicate that this is an Amazon DE marketplace.
     */
    public function amazonDe(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_id' => Platform::where('name', 'Amazon KDP')->first()->id ?? Platform::factory()->kdp()->create()->id,
            'code' => 'amazon_de',
            'name' => 'Amazon.de',
            'currency' => 'EUR',
        ]);
    }

    /**
     * Indicate that this is an Apple Books US marketplace.
     */
    public function appleBooksUs(): static
    {
        return $this->state(fn (array $attributes) => [
            'platform_id' => Platform::where('name', 'Apple Books')->first()->id ?? Platform::factory()->appleBooks()->create()->id,
            'code' => 'apple_us',
            'name' => 'Apple Books US',
            'currency' => 'USD',
        ]);
    }

    /**
     * Indicate that this marketplace uses EUR currency.
     */
    public function euro(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'EUR',
        ]);
    }

    /**
     * Indicate that this marketplace uses GBP currency.
     */
    public function gbp(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'GBP',
        ]);
    }

    /**
     * Indicate that this marketplace uses USD currency.
     */
    public function usd(): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => 'USD',
        ]);
    }
}
