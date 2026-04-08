import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityFaviconController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityFaviconController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-favicons/{entityFavicon}'
*/
export const show = (args: { tenant: string | number, entityFavicon: string | number | { id: string | number } } | [tenant: string | number, entityFavicon: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/entity-favicons/{entityFavicon}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityFaviconController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityFaviconController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-favicons/{entityFavicon}'
*/
show.url = (args: { tenant: string | number, entityFavicon: string | number | { id: string | number } } | [tenant: string | number, entityFavicon: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            entityFavicon: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        entityFavicon: typeof args.entityFavicon === 'object'
        ? args.entityFavicon.id
        : args.entityFavicon,
    }

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{entityFavicon}', parsedArgs.entityFavicon.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityFaviconController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityFaviconController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-favicons/{entityFavicon}'
*/
show.get = (args: { tenant: string | number, entityFavicon: string | number | { id: string | number } } | [tenant: string | number, entityFavicon: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityFaviconController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityFaviconController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-favicons/{entityFavicon}'
*/
show.head = (args: { tenant: string | number, entityFavicon: string | number | { id: string | number } } | [tenant: string | number, entityFavicon: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityFaviconController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityFaviconController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-favicons/{entityFavicon}'
*/
const showForm = (args: { tenant: string | number, entityFavicon: string | number | { id: string | number } } | [tenant: string | number, entityFavicon: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityFaviconController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityFaviconController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-favicons/{entityFavicon}'
*/
showForm.get = (args: { tenant: string | number, entityFavicon: string | number | { id: string | number } } | [tenant: string | number, entityFavicon: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityFaviconController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityFaviconController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-favicons/{entityFavicon}'
*/
showForm.head = (args: { tenant: string | number, entityFavicon: string | number | { id: string | number } } | [tenant: string | number, entityFavicon: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const entityFavicon = {
    show: Object.assign(show, show),
}

export default entityFavicon