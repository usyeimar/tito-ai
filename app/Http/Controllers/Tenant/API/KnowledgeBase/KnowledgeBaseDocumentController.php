<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\KnowledgeBase;

use App\Actions\Tenant\KnowledgeBase\CreateKnowledgeBaseDocument;
use App\Actions\Tenant\KnowledgeBase\DeleteKnowledgeBaseDocument;
use App\Actions\Tenant\KnowledgeBase\ListKnowledgeBaseDocuments;
use App\Actions\Tenant\KnowledgeBase\ShowKnowledgeBaseDocument;
use App\Actions\Tenant\KnowledgeBase\UpdateKnowledgeBaseDocument;
use App\Data\Tenant\KnowledgeBase\CreateKnowledgeBaseDocumentData;
use App\Data\Tenant\KnowledgeBase\KnowledgeBaseDocumentData;
use App\Data\Tenant\KnowledgeBase\UpdateKnowledgeBaseDocumentData;
use App\Http\Controllers\Concerns\PaginatesJsonResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\API\KnowledgeBase\IndexKnowledgeBaseDocumentRequest;
use App\Http\Requests\Tenant\API\KnowledgeBase\StoreKnowledgeBaseDocumentRequest;
use App\Http\Requests\Tenant\API\KnowledgeBase\UpdateKnowledgeBaseDocumentRequest;
use App\Models\Tenant\KnowledgeBase\KnowledgeBase;
use App\Models\Tenant\KnowledgeBase\KnowledgeBaseDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class KnowledgeBaseDocumentController extends Controller
{
    use PaginatesJsonResponses;

    public function index(
        IndexKnowledgeBaseDocumentRequest $request,
        KnowledgeBase $knowledgeBase,
        ListKnowledgeBaseDocuments $action,
    ): JsonResponse {
        Gate::authorize('viewAny', KnowledgeBase::class);

        $paginator = $action($knowledgeBase, $request->validated());

        return $this->paginatedJson(
            $paginator,
            fn (KnowledgeBaseDocument $doc) => KnowledgeBaseDocumentData::fromModel($doc),
        );
    }

    public function store(
        StoreKnowledgeBaseDocumentRequest $request,
        KnowledgeBase $knowledgeBase,
        CreateKnowledgeBaseDocument $action,
    ): JsonResponse {
        Gate::authorize('create', KnowledgeBase::class);

        $data = CreateKnowledgeBaseDocumentData::from($request->validated());
        $result = $action($data);

        return response()->json(['data' => $result, 'message' => 'Knowledge base document created.'], 201);
    }

    public function show(
        KnowledgeBase $knowledgeBase,
        KnowledgeBaseDocument $document,
        ShowKnowledgeBaseDocument $action,
    ): JsonResponse {
        Gate::authorize('view', KnowledgeBase::class);

        return response()->json(['data' => $action($document)]);
    }

    public function update(
        UpdateKnowledgeBaseDocumentRequest $request,
        KnowledgeBase $knowledgeBase,
        KnowledgeBaseDocument $document,
        UpdateKnowledgeBaseDocument $action,
    ): JsonResponse {
        Gate::authorize('update', KnowledgeBase::class);

        $data = UpdateKnowledgeBaseDocumentData::from($request->validated());
        $result = $action($document, $data);

        return response()->json(['data' => $result, 'message' => 'Knowledge base document updated.']);
    }

    public function destroy(
        KnowledgeBase $knowledgeBase,
        KnowledgeBaseDocument $document,
        DeleteKnowledgeBaseDocument $action,
    ): Response {
        Gate::authorize('delete', KnowledgeBase::class);

        $action($document);

        return response()->noContent();
    }
}
