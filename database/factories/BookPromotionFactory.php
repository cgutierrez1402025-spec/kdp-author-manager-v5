<?php

namespace Database\Factories;

use App\Models\BookPromotion;
use App\Models\KdpSelectPeriod;
use App\Models\Marketplace;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookPromotion>
 */
class BookPromotionFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = BookPromotion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-3 months', '+1 month');
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 months');

        return [
            'publication_id' => Publication::factory(),
            'marketplace_id' => Marketplace::factory(),
            'kdp_select_period_id' => KdpSelectPeriod::factory(),
            'promotion_type' => $this->faker->randomElement(['free', 'kindle_countdown', 'price_promo']),
            'promotion_name' => $this->faker->sentence(3),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'normal_price' => $this->faker->randomFloat(2, 0.99, 19.99),
            'promotional_price' => $this->faker->randomFloat(2, 0, 9.99),
            'status' => $this->faker->randomElement(['planned', 'active', 'completed', 'cancelled']),
            'objective' => $this->faker->randomElement(['sales_boost', 'ranking_improvement', 'reviews_increase', 'newsletter_signups']),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that this is a free promotion.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'promotion_type' => 'free',
            'promotional_price' => 0,
        ]);
    }

    /**
     * Indicate that this is a Kindle Countdown promotion.
     */
    public function kindleCountdown(): static
    {
        return $this->state(fn (array $attributes) => [
            'promotion_type' => 'kindle_countdown',
        ]);
    }

    /**
     * Indicate that this is a price promotion.
     */
    public function pricePromo(): static
    {
        return $this->state(fn (array $attributes) => [
            'promotion_type' => 'price_promo',
        ]);
    }

    /**
     * Indicate that this promotion is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ]);
    }

    /**
     * Indicate that this promotion is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'end_date' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
        ]);
    }

    /**
     * Indicate that this promotion is planned.
     */
    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'planned',
            'start_date' => $this->faker->dateTimeBetween('now', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
        ]);
    }

    /**
     * Indicate that this promotion has high sales.
     */
    public function highSales(): static
    {
        return $this->state(fn (array $attributes) => [
            'normal_price' => $this->faker->randomFloat(2, 9.99, 19.99),
            'promotional_price' => $this->faker->randomFloat(2, 0, 4.99),
        ]);
    }

    /**
     * Indicate that this promotion has no promotional price (free).
     */
    public function freePromo(): static
    {
        return $this->state(fn (array $attributes) => [
            'promotional_price' => 0,
        ]);
    }
}
