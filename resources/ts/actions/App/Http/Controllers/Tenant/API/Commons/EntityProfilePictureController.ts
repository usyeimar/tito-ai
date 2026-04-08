import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityProfilePictureController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityProfilePictureController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-profile-pictures/{entityProfilePicture}'
*/
export const show = (args: { tenant: string | number, entityProfilePicture: string | number | { id: string | number } } | [tenant: string | number, entityProfilePicture: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/entity-profile-pictures/{entityProfilePicture}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityProfilePictureController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityProfilePictureController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-profile-pictures/{entityProfilePicture}'
*/
show.url = (args: { tenant: string | number, entityProfilePicture: string | number | { id: string | number } } | [tenant: string | number, entityProfilePicture: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            entityProfilePicture: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        entityProfilePicture: typeof args.entityProfilePicture === 'object'
        ? args.entityProfilePicture.id
        : args.entityProfilePicture,
    }

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{entityProfilePicture}', parsedArgs.entityProfilePicture.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityProfilePictureController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityProfilePictureController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-profile-pictures/{entityProfilePicture}'
*/
show.get = (args: { tenant: string | number, entityProfilePicture: string | number | { id: string | number } } | [tenant: string | number, entityProfilePicture: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityProfilePictureController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityProfilePictureController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-profile-pictures/{entityProfilePicture}'
*/
show.head = (args: { tenant: string | number, entityProfilePicture: string | number | { id: string | number } } | [tenant: string | number, entityProfilePicture: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityProfilePictureController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityProfilePictureController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-profile-pictures/{entityProfilePicture}'
*/
const showForm = (args: { tenant: string | number, entityProfilePicture: string | number | { id: string | number } } | [tenant: string | number, entityProfilePicture: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityProfilePictureController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityProfilePictureController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-profile-pictures/{entityProfilePicture}'
*/
showForm.get = (args: { tenant: string | number, entityProfilePicture: string | number | { id: string | number } } | [tenant: string | number, entityProfilePicture: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Commons\EntityProfilePictureController::show
* @see app/Http/Controllers/Tenant/API/Commons/EntityProfilePictureController.php:14
* @route 'https://app.tito.ai/{tenant}/api/entity-profile-pictures/{entityProfilePicture}'
*/
showForm.head = (args: { tenant: string | number, entityProfilePicture: string | number | { id: string | number } } | [tenant: string | number, entityProfilePicture: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const EntityProfilePictureController = { show }

export default EntityProfilePictureController