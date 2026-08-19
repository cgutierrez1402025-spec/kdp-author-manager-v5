<?php

namespace Database\Factories;

use App\Models\Work;
use App\Models\WorkLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkLanguage>
 */
class WorkLanguageFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = WorkLanguage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_id' => Work::factory(), // Creates and associates a work
            'language_code' => $this->faker->randomElement(['en', 'es', 'fr', 'de', 'it', 'pt', 'ja', 'zh', 'ru', 'ar']),
            'translation_status' => $this->faker->randomElement(['original', 'translation', 'in_progress', 'proofread', 'final']),
            'translator_name' => $this->faker->name(),
            'notes' => $this->faker->sentence(),
        ];
    }

    /**
     * Indicate that this is the original language.
     */
    public function original(): static
    {
        return $this->state(fn (array $attributes) => [
            'language_code' => 'en',
            'translation_status' => 'original',
        ]);
    }

    /**
     * Indicate that this is a translation.
     */
    public function translated(): static
    {
        return $this->state(fn (array $attributes) => [
            'translation_status' => 'translation',
        ]);
    }
}
