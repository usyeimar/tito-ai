import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents'
*/
export const index = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents'
*/
index.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            knowledgeBase: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        knowledgeBase: typeof args.knowledgeBase === 'object'
        ? args.knowledgeBase.id
        : args.knowledgeBase,
    }

    return index.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents'
*/
index.get = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents'
*/
index.head = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:28
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents'
*/
export const store = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:28
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents'
*/
store.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            knowledgeBase: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        knowledgeBase: typeof args.knowledgeBase === 'object'
        ? args.knowledgeBase.id
        : args.knowledgeBase,
    }

    return store.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:28
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents'
*/
store.post = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::show
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:59
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
export const show = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::show
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:59
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
show.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            knowledgeBase: args[1],
            document: args[2],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        knowledgeBase: typeof args.knowledgeBase === 'object'
        ? args.knowledgeBase.id
        : args.knowledgeBase,
        document: typeof args.document === 'object'
        ? args.document.id
        : args.document,
    }

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace('{document}', parsedArgs.document.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::show
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:59
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
show.get = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::show
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:59
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
show.head = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:64
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
export const update = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:64
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
update.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            knowledgeBase: args[1],
            document: args[2],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        knowledgeBase: typeof args.knowledgeBase === 'object'
        ? args.knowledgeBase.id
        : args.knowledgeBase,
        document: typeof args.document === 'object'
        ? args.document.id
        : args.document,
    }

    return update.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace('{document}', parsedArgs.document.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:64
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
update.patch = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:103
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
export const destroy = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:103
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
destroy.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            knowledgeBase: args[1],
            document: args[2],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        knowledgeBase: typeof args.knowledgeBase === 'object'
        ? args.knowledgeBase.id
        : args.knowledgeBase,
        document: typeof args.document === 'object'
        ? args.document.id
        : args.document,
    }

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace('{document}', parsedArgs.document.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseDocumentController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseDocumentController.php:103
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/documents/{document}'
*/
destroy.delete = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, document: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const KnowledgeBaseDocumentController = { index, store, show, update, destroy }

export default KnowledgeBaseDocumentController