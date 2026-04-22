<?php

use App\Jobs\Tenant\KnowledgeBase\IngestKnowledgeBaseDocument;
use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseCategory;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseDocument;
use Laravel\Ai\Files;
use Laravel\Ai\Stores;

beforeEach(function (): void {
    Stores::fake();
    Files::fake();
});

describe('IngestKnowledgeBaseDocument job', function () {
    it('creates a vector store when knowledge base has none and adds the document file', function () {
        $kb = KnowledgeBase::factory()->create(['name' => 'Support Docs', 'vector_store_id' => null]);
        $category = KnowledgeBaseCategory::factory()->create(['knowledge_base_id' => $kb->id]);
        $document = KnowledgeBaseDocument::factory()->create([
            'knowledge_base_category_id' => $category->id,
            'title' => 'Getting Started',
            'indexing_status' => 'pending',
        ]);
        $document->versions()->create([
            'version_number' => 1,
            'content' => '## Hello world',
            'author_id' => $this->user->id,
            'change_summary' => 'Initial',
        ]);

        (new IngestKnowledgeBaseDocument($document->id))->handle();

        Stores::assertCreated('Support Docs');

        $document->refresh();
        $kb->refresh();

        expect($kb->vector_store_id)->not->toBeNull();
        expect($document->indexing_status)->toBe('indexed');
        expect($document->vector_store_file_id)->not->toBeNull();
        expect($document->indexed_at)->not->toBeNull();
    });

    it('reuses an existing vector store id on the knowledge base', function () {
        $kb = KnowledgeBase::factory()->create(['vector_store_id' => 'vs_existing_123']);
        $category = KnowledgeBaseCategory::factory()->create(['knowledge_base_id' => $kb->id]);
        $document = KnowledgeBaseDocument::factory()->create([
            'knowledge_base_category_id' => $category->id,
            'title' => 'Pricing',
        ]);
        $document->versions()->create([
            'version_number' => 1,
            'content' => 'Price list',
            'author_id' => $this->user->id,
            'change_summary' => 'Initial',
        ]);

        (new IngestKnowledgeBaseDocument($document->id))->handle();

        Stores::assertNotCreated('Pricing');
        expect($document->fresh()->vector_store_file_id)->not->toBeNull();
    });
});
