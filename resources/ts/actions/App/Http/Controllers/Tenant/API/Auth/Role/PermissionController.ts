import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\PermissionController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/PermissionController.php:13
* @route 'https://app.tito.ai/{tenant}/api/permissions'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/permissions',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\PermissionController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/PermissionController.php:13
* @route 'https://app.tito.ai/{tenant}/api/permissions'
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
* @see \App\Http\Controllers\Tenant\API\Auth\Role\PermissionController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/PermissionController.php:13
* @route 'https://app.tito.ai/{tenant}/api/permissions'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\PermissionController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/PermissionController.php:13
* @route 'https://app.tito.ai/{tenant}/api/permissions'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\PermissionController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/PermissionController.php:13
* @route 'https://app.tito.ai/{tenant}/api/permissions'
*/
const indexForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\PermissionController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/PermissionController.php:13
* @route 'https://app.tito.ai/{tenant}/api/permissions'
*/
indexForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\PermissionController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/PermissionController.php:13
* @route 'https://app.tito.ai/{tenant}/api/permissions'
*/
indexForm.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

const PermissionController = { index }

export default PermissionController