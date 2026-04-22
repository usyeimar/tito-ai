<?php

declare(strict_types=1);

namespace App\Actions\Tenant\KnowledgeBase;

use App\Data\Tenant\KnowledgeBase\CreateKnowledgeBaseData;
use App\Data\Tenant\KnowledgeBase\KnowledgeBaseData;
use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Ai\Stores;
use Throwable;

final class CreateKnowledgeBase
{
    public function __invoke(CreateKnowledgeBaseData $data): KnowledgeBaseData
    {
        $knowledgeBase = KnowledgeBase::create([
            'name' => $data->name,
            'slug' => Str::slug($data->name).'-'.Str::random(5),
            'description' => $data->description,
            'is_public' => $data->is_public,
        ]);

        try {
            $store = Stores::create($knowledgeBase->name, description: $knowledgeBase->description);
            $knowledgeBase->forceFill(['vector_store_id' => $store->id])->save();
        } catch (Throwable $e) {
            Log::warning('Unable to provision vector store for knowledge base; will lazily create on first ingest.', [
                'knowledge_base_id' => $knowledgeBase->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return KnowledgeBaseData::from($knowledgeBase);
    }
}
