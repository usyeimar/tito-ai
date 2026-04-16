import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:18
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories'
*/
export const index = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:18
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories'
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
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:18
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories'
*/
index.get = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:18
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories'
*/
index.head = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:25
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories'
*/
export const store = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:25
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories'
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
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:25
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories'
*/
store.post = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories/{category}'
*/
export const update = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories/{category}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories/{category}'
*/
update.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            knowledgeBase: args[1],
            category: args[2],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        knowledgeBase: typeof args.knowledgeBase === 'object'
        ? args.knowledgeBase.id
        : args.knowledgeBase,
        category: typeof args.category === 'object'
        ? args.category.id
        : args.category,
    }

    return update.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories/{category}'
*/
update.patch = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:51
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories/{category}'
*/
export const destroy = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories/{category}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:51
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories/{category}'
*/
destroy.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            knowledgeBase: args[1],
            category: args[2],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        knowledgeBase: typeof args.knowledgeBase === 'object'
        ? args.knowledgeBase.id
        : args.knowledgeBase,
        category: typeof args.category === 'object'
        ? args.category.id
        : args.category,
    }

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace('{category}', parsedArgs.category.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseCategoryController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseCategoryController.php:51
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}/categories/{category}'
*/
destroy.delete = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number }, category: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const KnowledgeBaseCategoryController = { index, store, update, destroy }

export default KnowledgeBaseCategoryController