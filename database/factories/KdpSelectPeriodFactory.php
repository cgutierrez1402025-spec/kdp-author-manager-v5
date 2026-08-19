<?php

namespace Database\Factories;

use App\Models\KdpSelectPeriod;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KdpSelectPeriod>
 */
class KdpSelectPeriodFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = KdpSelectPeriod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $endDate = $this->faker->dateTimeBetween($startDate, '+6 months');

        return [
            'publication_id' => Publication::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'auto_renewal' => $this->faker->boolean(30), // 30% chance
            'free_promo_days_allowed' => $this->faker->randomElement([0, 2, 5, 7]),
            'free_promo_days_used' => $this->faker->numberBetween(0, 5),
            'free_promo_days_remaining' => $this->faker->numberBetween(0, 5),
            'status' => $this->faker->randomElement(['active', 'expired', 'cancelled', 'pending']),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that this period is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_date' => $this->faker->dateTimeBetween('-3 months', 'now'),
            'end_date' => $this->faker->dateTimeBetween('now', '+3 months'),
            'free_promo_days_remaining' => $this->faker->numberBetween(0, 5),
        ]);
    }

    /**
     * Indicate that this period is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'end_date' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'free_promo_days_remaining' => 0,
        ]);
    }

    /**
     * Indicate that this period has auto-renewal enabled.
     */
    public function autoRenewal(): static
    {
        return $this->state(fn (array $attributes) => [
            'auto_renewal' => true,
        ]);
    }

    /**
     * Indicate that this period has max free promo days.
     */
    public function maxFreeDays(): static
    {
        return $this->state(fn (array $attributes) => [
            'free_promo_days_allowed' => 7,
            'free_promo_days_used' => $this->faker->numberBetween(0, 7),
            'free_promo_days_remaining' => $this->faker->numberBetween(0, 7),
        ]);
    }

    /**
     * Indicate that this period has no free promo days used.
     */
    public function noFreeDaysUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'free_promo_days_used' => 0,
            'free_promo_days_remaining' => $this->faker->numberBetween(0, 7),
        ]);
    }

    /**
     * Indicate that this period has all free promo days used.
     */
    public function allFreeDaysUsed(): static
    {
        return $this->state(fn (array $attributes) => [
            'free_promo_days_used' => $this->faker->numberBetween(5, 7),
            'free_promo_days_remaining' => 0,
        ]);
    }
}
