import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\Web\KnowledgeBase\KnowledgeBasePageController::index
* @see app/Http/Controllers/Tenant/Web/KnowledgeBase/KnowledgeBasePageController.php:14
* @route 'https://app.tito.ai/{tenant}/knowledge'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/knowledge',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\Web\KnowledgeBase\KnowledgeBasePageController::index
* @see app/Http/Controllers/Tenant/Web/KnowledgeBase/KnowledgeBasePageController.php:14
* @route 'https://app.tito.ai/{tenant}/knowledge'
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
* @see \App\Http\Controllers\Tenant\Web\KnowledgeBase\KnowledgeBasePageController::index
* @see app/Http/Controllers/Tenant/Web/KnowledgeBase/KnowledgeBasePageController.php:14
* @route 'https://app.tito.ai/{tenant}/knowledge'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\Web\KnowledgeBase\KnowledgeBasePageController::index
* @see app/Http/Controllers/Tenant/Web/KnowledgeBase/KnowledgeBasePageController.php:14
* @route 'https://app.tito.ai/{tenant}/knowledge'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

const knowledge = {
    index: Object.assign(index, index),
}

export default knowledge