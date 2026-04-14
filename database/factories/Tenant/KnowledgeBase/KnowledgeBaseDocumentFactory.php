<?php

declare(strict_types=1);

namespace Database\Factories\Tenant\KnowledgeBase;

use App\Models\Tenant\Auth\Authentication\User;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseCategory;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeBaseDocument>
 */
class KnowledgeBaseDocumentFactory extends Factory
{
    protected $model = KnowledgeBaseDocument::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'knowledge_base_category_id' => KnowledgeBaseCategory::factory(),
            'title' => ucfirst($title),
            'slug' => Str::slug($title).'-'.$this->faker->randomNumber(5),
            'content_format' => $this->faker->randomElement(['markdown', 'html', 'plain']),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'author_id' => User::factory(),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
