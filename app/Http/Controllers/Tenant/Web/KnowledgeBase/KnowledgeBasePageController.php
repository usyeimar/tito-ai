<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Web\KnowledgeBase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeBasePageController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('tenant/knowledge/index', [
            'tenant' => [
                'id' => tenant('id'),
                'name' => tenant('name'),
                'slug' => tenant('slug'),
            ],
            // In a real app we'd fetch categories/documents here
            'documents' => []
        ]);
    }
}
