<?php

declare(strict_types=1);

namespace Database\Factories\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<KnowledgeBaseCategory>
 */
class KnowledgeBaseCategoryFactory extends Factory
{
    protected $model = KnowledgeBaseCategory::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'knowledge_base_id' => KnowledgeBase::factory(),
            'parent_id' => null,
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.$this->faker->randomNumber(5),
            'display_order' => $this->faker->numberBetween(0, 100),
        ];
    }
}
