<?php

use App\Jobs\Tenant\KnowledgeBase\IngestKnowledgeBaseDocument;
use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseCategory;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseDocument;
use Illuminate\Support\Facades\Bus;

beforeEach(function (): void {
    Bus::fake([IngestKnowledgeBaseDocument::class]);
});

describe('Knowledge Base Document API', function () {
    describe('Authentication', function () {
        it('requires authentication to list documents', function () {
            $kb = KnowledgeBase::factory()->create();

            $response = $this->getJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents"));
            $response->assertUnauthorized();
        });
    });

    describe('Document Management', function () {
        describe('List', function () {
            it('lists documents for a knowledge base', function () {
                $kb = KnowledgeBase::factory()->create();
                $category = KnowledgeBaseCategory::factory()->create(['knowledge_base_id' => $kb->id]);
                KnowledgeBaseDocument::factory()->count(3)->create(['knowledge_base_category_id' => $category->id]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents"));

                $response->assertOk();
            });
        });

        describe('Show', function () {
            it('shows a document', function () {
                $kb = KnowledgeBase::factory()->create();
                $category = KnowledgeBaseCategory::factory()->create(['knowledge_base_id' => $kb->id]);
                $document = KnowledgeBaseDocument::factory()->create([
                    'knowledge_base_category_id' => $category->id,
                    'title' => 'Test Document',
                ]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents/{$document->id}"));

                $response->assertOk();
                $response->assertJsonPath('data.id', (string) $document->id);
                $response->assertJsonPath('data.title', 'Test Document');
            });

            it('returns 404 for non-existent document', function () {
                $kb = KnowledgeBase::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->getJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents/01HX99999999999999999999999"));

                $response->assertNotFound();
            });
        });

        describe('Create', function () {
            it('creates a document', function () {
                $kb = KnowledgeBase::factory()->create();
                $category = KnowledgeBaseCategory::factory()->create(['knowledge_base_id' => $kb->id]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->postJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents"), [
                        'knowledge_base_category_id' => (string) $category->id,
                        'title' => 'New Document',
                        'content' => '# Hello World',
                        'content_format' => 'markdown',
                    ]);

                $response->assertCreated();
                $response->assertJsonPath('data.title', 'New Document');
                $response->assertJsonPath('data.status', 'draft');
            });

            it('requires category to create document', function () {
                $kb = KnowledgeBase::factory()->create();

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->postJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents"), [
                        'title' => 'No Category',
                        'content' => 'Content',
                    ]);

                $response->assertUnprocessable();
                assertHasValidationError($response, 'knowledge_base_category_id');
            });

            it('requires title to create document', function () {
                $kb = KnowledgeBase::factory()->create();
                $category = KnowledgeBaseCategory::factory()->create(['knowledge_base_id' => $kb->id]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->postJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents"), [
                        'knowledge_base_category_id' => (string) $category->id,
                        'content' => 'Content',
                    ]);

                $response->assertUnprocessable();
                assertHasValidationError($response, 'title');
            });
        });

        describe('Update', function () {
            it('updates a document', function () {
                $kb = KnowledgeBase::factory()->create();
                $category = KnowledgeBaseCategory::factory()->create(['knowledge_base_id' => $kb->id]);
                $document = KnowledgeBaseDocument::factory()->create([
                    'knowledge_base_category_id' => $category->id,
                    'title' => 'Original Title',
                ]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->patchJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents/{$document->id}"), [
                        'title' => 'Updated Title',
                    ]);

                $response->assertOk();
                $response->assertJsonPath('data.title', 'Updated Title');

                expect($document->fresh()->title)->toBe('Updated Title');
            });
        });

        describe('Delete', function () {
            it('deletes a document', function () {
                $kb = KnowledgeBase::factory()->create();
                $category = KnowledgeBaseCategory::factory()->create(['knowledge_base_id' => $kb->id]);
                $document = KnowledgeBaseDocument::factory()->create(['knowledge_base_category_id' => $category->id]);

                $response = $this->actingAs($this->user, 'tenant-api')
                    ->deleteJson($this->tenantApiUrl("ai/knowledge-bases/{$kb->id}/documents/{$document->id}"));

                $response->assertNoContent();
                expect(KnowledgeBaseDocument::query()->whereKey($document->id)->exists())->toBeFalse();
            });
        });
    });
});
