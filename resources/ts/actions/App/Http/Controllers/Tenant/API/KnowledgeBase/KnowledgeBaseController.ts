import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:17
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:17
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases'
*/
index.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tenant: args }
    }

    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
    }

    return index.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:17
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::index
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:17
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:24
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases'
*/
export const store = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:24
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases'
*/
store.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tenant: args }
    }

    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
    }

    return store.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::store
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:24
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases'
*/
store.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::show
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:34
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
export const show = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::show
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:34
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
show.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::show
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:34
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
show.get = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::show
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:34
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
show.head = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:39
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
export const update = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:39
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
update.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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

    return update.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::update
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:39
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
update.patch = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:52
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
export const destroy = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:52
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
destroy.url = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
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

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{knowledgeBase}', parsedArgs.knowledgeBase.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\KnowledgeBase\KnowledgeBaseController::destroy
* @see app/Http/Controllers/Tenant/API/KnowledgeBase/KnowledgeBaseController.php:52
* @route 'https://app.tito.ai/{tenant}/api/ai/knowledge-bases/{knowledgeBase}'
*/
destroy.delete = (args: { tenant: string | number, knowledgeBase: string | number | { id: string | number } } | [tenant: string | number, knowledgeBase: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

const KnowledgeBaseController = { index, store, show, update, destroy }

export default KnowledgeBaseController