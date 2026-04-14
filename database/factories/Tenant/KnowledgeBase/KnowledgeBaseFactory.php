<?php

declare(strict_types=1);

namespace Database\Factories\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeBase>
 */
class KnowledgeBaseFactory extends Factory
{
    protected $model = KnowledgeBase::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.$this->faker->randomNumber(5),
            'description' => $this->faker->sentence(),
            'is_public' => $this->faker->boolean(80),
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
}
