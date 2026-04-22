<?php

declare(strict_types=1);

namespace App\Jobs\Tenant\KnowledgeBase;

use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Files\Document;
use Laravel\Ai\Stores;
use Throwable;

final class IngestKnowledgeBaseDocument implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var array<int> */
    public array $backoff = [10, 30, 120];

    public function __construct(public string $documentId) {}

    public function handle(): void
    {
        $document = KnowledgeBaseDocument::query()
            ->with(['category.knowledgeBase', 'versions' => fn ($q) => $q->latest('version_number')->limit(1)])
            ->findOrFail($this->documentId);

        $knowledgeBase = $document->category?->knowledgeBase;
        $latestVersion = $document->versions->first();

        if ($knowledgeBase === null || $latestVersion === null) {
            $document->forceFill([
                'indexing_status' => 'failed',
                'indexing_error' => 'Missing knowledge base or document version.',
            ])->save();

            return;
        }

        try {
            $storeId = $this->ensureVectorStore($knowledgeBase);
            $store = Stores::get($storeId);

            if ($document->vector_store_file_id) {
                $store->remove($document->vector_store_file_id, deleteFile: true);
            }

            $payload = $this->buildMarkdownPayload($document->title, (string) $latestVersion->content);
            $file = Document::fromString($payload, 'text/markdown')->put();

            $store->add($file->id);

            $document->forceFill([
                'vector_store_file_id' => $file->id,
                'indexing_status' => 'indexed',
                'indexing_error' => null,
                'indexed_at' => now(),
            ])->save();
        } catch (Throwable $e) {
            Log::error('Failed to ingest knowledge base document into vector store.', [
                'document_id' => $document->id,
                'exception' => $e->getMessage(),
            ]);

            $document->forceFill([
                'indexing_status' => 'failed',
                'indexing_error' => $e->getMessage(),
            ])->save();

            throw $e;
        }
    }

    private function ensureVectorStore(KnowledgeBase $kb): string
    {
        if ($kb->vector_store_id) {
            return $kb->vector_store_id;
        }

        $store = Stores::create($kb->name, description: $kb->description);
        $kb->forceFill(['vector_store_id' => $store->id])->save();

        return $store->id;
    }

    private function buildMarkdownPayload(string $title, string $content): string
    {
        return "# {$title}\n\n".trim($content)."\n";
    }
}
