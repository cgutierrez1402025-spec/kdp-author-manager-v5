<?php

namespace Database\Factories;

use App\Models\KdpMetadata;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KdpMetadata>
 */
class KdpMetadataFactory extends Factory
{
    /**
     * The model associated with the factory.
     *
     * @var string
     */
    protected $model = KdpMetadata::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'publication_id' => Publication::factory(),
            'title' => $this->faker->sentence(4),
            'subtitle' => $this->faker->optional()->sentence(4),
            'author' => $this->faker->name(),
            'contributors' => $this->faker->optional()->json([
                ['name' => $this->faker->name(), 'role' => 'editor'],
                ['name' => $this->faker->name(), 'role' => 'illustrator'],
            ]),
            'series_name' => $this->faker->optional()->sentence(2),
            'series_number' => $this->faker->optional()->numberBetween(1, 20),
            'description' => $this->faker->paragraph(2),
            'keywords' => $this->faker->optional()->sentence(4),
            'categories' => $this->faker->optional()->json([
                'Fiction > Mystery',
                'Fiction > Thriller',
            ]),
            'age_range' => $this->faker->optional()->randomElement([
                '0-2 years',
                '3-5 years',
                '6-8 years',
                '9-12 years',
                'Teens',
                'Adults',
            ]),
            'rights' => $this->faker->optional()->sentence(),
            'ai_declaration' => $this->faker->optional()->randomElement([
                'No AI was used in the creation of this work.',
                'AI-assisted writing was used for brainstorming and outline creation.',
                'AI-generated text was used and subsequently edited by a human author.',
                'This work was created entirely by AI.',
            ]),
        ];
    }

    /**
     * Indicate that this metadata has contributors.
     */
    public function withContributors(): static
    {
        return $this->state(fn (array $attributes) => [
            'contributors' => json_encode([
                ['name' => 'John Doe', 'role' => 'Editor'],
                ['name' => 'Jane Smith', 'role' => 'Illustrator'],
            ]),
        ]);
    }

    /**
     * Indicate that this metadata is for a series.
     */
    public function series(): static
    {
        return $this->state(fn (array $attributes) => [
            'series_name' => $this->faker->sentence(2),
            'series_number' => $this->faker->numberBetween(1, 20),
        ]);
    }

    /**
     * Indicate that this metadata has categories.
     */
    public function withCategories(): static
    {
        return $this->state(fn (array $attributes) => [
            'categories' => json_encode([
                'Fiction > Mystery',
                'Fiction > Thriller > Psychological',
                'Fiction > Crime',
            ]),
        ]);
    }

    /**
     * Indicate that this metadata has an AI declaration.
     */
    public function withAiDeclaration(): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_declaration' => 'AI-assisted writing was used for brainstorming and outline creation.',
        ]);
    }

    /**
     * Indicate that this metadata has no AI declaration.
     */
    public function withoutAiDeclaration(): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_declaration' => null,
        ]);
    }
}
