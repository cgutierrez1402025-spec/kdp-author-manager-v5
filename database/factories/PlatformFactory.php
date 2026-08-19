<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Platform>
 */
class PlatformFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = Platform::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Amazon KDP',
                'Apple Books',
                'Google Play Books',
                'Kobo Writing Life',
                'Nook Press',
                'Smashwords',
                'Draft2Digital',
                'PublishDrive',
                'StreetLib',
                'IngramSpark',
            ]),
            'description' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that this is Amazon KDP.
     */
    public function kdp(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Amazon KDP',
            'description' => 'Amazon Kindle Direct Publishing',
        ]);
    }

    /**
     * Indicate that this is Apple Books.
     */
    public function appleBooks(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Apple Books',
            'description' => 'Apple Books for Authors',
        ]);
    }

    /**
     * Indicate that this is Google Play Books.
     */
    public function googlePlayBooks(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Google Play Books',
            'description' => 'Google Play Books Partner Center',
        ]);
    }

    /**
     * Indicate that this is Kobo Writing Life.
     */
    public function koboWritingLife(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Kobo Writing Life',
            'description' => 'Kobo Writing Life Platform',
        ]);
    }
}
