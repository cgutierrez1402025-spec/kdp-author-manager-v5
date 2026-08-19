<?php

namespace Database\Factories;

use App\Models\BookPromotion;
use App\Models\PromotionDailyResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromotionDailyResult>
 */
class PromotionDailyResultFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = PromotionDailyResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'book_promotion_id' => BookPromotion::factory(),
            'date' => $date,
            'paid_units' => $this->faker->numberBetween(0, 100),
            'free_units_promo' => $this->faker->numberBetween(0, 50),
            'free_units_price_match' => $this->faker->numberBetween(0, 20),
            'kenp_pages_read' => $this->faker->numberBetween(0, 5000),
            'gross_royalties' => $this->faker->randomFloat(2, 0, 500),
            'net_royalties' => $this->faker->randomFloat(2, 0, 400),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD']),
            'ranking_position' => $this->faker->optional()->numberBetween(1, 100000),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that this is for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now(),
        ]);
    }

    /**
     * Indicate that this is for yesterday.
     */
    public function yesterday(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that this day had high sales.
     */
    public function highSales(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_units' => $this->faker->numberBetween(50, 200),
            'net_royalties' => $this->faker->randomFloat(2, 20, 100),
        ]);
    }

    /**
     * Indicate that this day had zero sales.
     */
    public function zeroSales(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_units' => 0,
            'free_units_promo' => 0,
            'free_units_price_match' => 0,
            'kenp_pages_read' => 0,
            'gross_royalties' => 0,
            'net_royalties' => 0,
        ]);
    }

    /**
     * Indicate that this day had high KENP pages read.
     */
    public function highKENP(): static
    {
        return $this->state(fn (array $attributes) => [
            'kenp_pages_read' => $this->faker->numberBetween(1000, 10000),
        ]);
    }
}
