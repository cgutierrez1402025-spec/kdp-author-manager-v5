<?php

namespace Database\Factories;

use App\Models\Publication;
use App\Models\RoyaltyEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoyaltyEntry>
 */
class RoyaltyEntryFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = RoyaltyEntry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'year' => $this->faker->year(),
            'month' => $this->faker->month(),
            'paid_units' => $this->faker->numberBetween(0, 10000),
            'free_units' => $this->faker->numberBetween(0, 1000),
            'kenp_pages' => $this->faker->numberBetween(0, 50000),
            'royalty_ebook' => $this->faker->randomFloat(2, 0, 5000),
            'royalty_paperback' => $this->faker->randomFloat(2, 0, 2000),
            'royalty_hardcover' => $this->faker->randomFloat(2, 0, 1000),
            'royalty_kenp' => $this->faker->randomFloat(2, 0, 1000),
            'total_royalty' => $this->faker->randomFloat(2, 0, 8000),
            'currency' => $this->faker->randomElement(['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD']),
            'source_file' => $this->faker->optional()->bothify('royalty_report_##.csv'),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that this is for January.
     */
    public function january(): static
    {
        return $this->state(fn (array $attributes) => [
            'month' => 1,
        ]);
    }

    /**
     * Indicate that this is for Q4.
     */
    public function q4(): static
    {
        return $this->state(fn (array $attributes) => [
            'month' => $this->faker->randomElement([10, 11, 12]),
        ]);
    }

    /**
     * Indicate that this is for the current year.
     */
    public function currentYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => now()->year,
        ]);
    }

    /**
     * Indicate that this is for last year.
     */
    public function lastYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => now()->subYear()->year,
        ]);
    }

    /**
     * Indicate that this entry has zero royalties.
     */
    public function zeroRoyalties(): static
    {
        return $this->state(fn (array $attributes) => [
            'royalty_ebook' => 0,
            'royalty_paperback' => 0,
            'royalty_hardcover' => 0,
            'royalty_kenp' => 0,
            'total_royalty' => 0,
        ]);
    }

    /**
     * Indicate that this entry has high KENP pages.
     */
    public function highKENP(): static
    {
        return $this->state(fn (array $attributes) => [
            'kenp_pages' => $this->faker->numberBetween(10000, 100000),
        ]);
    }
}
