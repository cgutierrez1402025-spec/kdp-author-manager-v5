<?php

namespace Database\Factories;

use App\Models\BookPromotion;
use App\Models\PromotionCost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromotionCost>
 */
class PromotionCostFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = PromotionCost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_promotion_id' => BookPromotion::factory(),
            'cost_type' => $this->faker->randomElement(['advertising', 'marketing', 'production', 'distribution', 'software', 'services', 'other']),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2, 5, 500),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD']),
            'date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that this is an advertising cost.
     */
    public function advertising(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_type' => 'advertising',
        ]);
    }

    /**
     * Indicate that this is a marketing cost.
     */
    public function marketing(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_type' => 'marketing',
        ]);
    }

    /**
     * Indicate that this is a production cost.
     */
    public function production(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_type' => 'production',
        ]);
    }

    /**
     * Indicate that this is a distribution cost.
     */
    public function distribution(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_type' => 'distribution',
        ]);
    }

    /**
     * Indicate that this is a software cost.
     */
    public function software(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_type' => 'software',
        ]);
    }

    /**
     * Indicate that this is a services cost.
     */
    public function services(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_type' => 'services',
        ]);
    }

    /**
     * Indicate that this cost is high amount.
     */
    public function highCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 100, 1000),
        ]);
    }

    /**
     * Indicate that this cost is low amount.
     */
    public function lowCost(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 1, 20),
        ]);
    }

    /**
     * Indicate that this cost is from today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => now(),
        ]);
    }
}
