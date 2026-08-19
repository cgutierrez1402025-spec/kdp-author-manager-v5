<?php

namespace Database\Factories;

use App\Models\Series;
use App\Models\User;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Work>
 */
class WorkFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = Work::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Creates and associates a user
            'title_internal' => $this->faker->sentence(3),
            'title_public' => $this->faker->sentence(3),
            'author_name' => $this->faker->name(),
            'original_language' => $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it', 'pt', 'ja', 'zh']),
            'status' => $this->faker->randomElement(['idea', 'outline', 'draft', 'review', 'final', 'published', 'cancelled', 'on_hold']),
            'series_id' => null, // Can be overridden with state
            'genre' => $this->faker->randomElement(['Fiction', 'Non-Fiction', 'Mystery', 'Romance', 'Sci-Fi', 'Fantasy', 'Thriller', 'Biography', 'History', 'Self-Help']),
            'target_audience' => $this->faker->randomElement(['Adult', 'Young Adult', 'Middle Grade', 'Children']),
            'description_marketing' => $this->faker->paragraph(3),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that the work is part of a series.
     */
    public function inSeries(): static
    {
        return $this->state(fn (array $attributes) => [
            'series_id' => Series::factory(), // Creates and associates a series
        ]);
    }

    /**
     * Indicate that the work is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the work is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }
}
