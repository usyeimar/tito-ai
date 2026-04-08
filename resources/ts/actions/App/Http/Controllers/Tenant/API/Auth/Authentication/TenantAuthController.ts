import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Auth\Authentication\TenantAuthController::me
* @see app/Http/Controllers/Tenant/API/Auth/Authentication/TenantAuthController.php:12
* @route 'https://app.tito.ai/{tenant}/api/auth/me'
*/
export const me = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(args, options),
    method: 'get',
})

me.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/auth/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Authentication\TenantAuthController::me
* @see app/Http/Controllers/Tenant/API/Auth/Authentication/TenantAuthController.php:12
* @route 'https://app.tito.ai/{tenant}/api/auth/me'
*/
me.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return me.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Authentication\TenantAuthController::me
* @see app/Http/Controllers/Tenant/API/Auth/Authentication/TenantAuthController.php:12
* @route 'https://app.tito.ai/{tenant}/api/auth/me'
*/
me.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Authentication\TenantAuthController::me
* @see app/Http/Controllers/Tenant/API/Auth/Authentication/TenantAuthController.php:12
* @route 'https://app.tito.ai/{tenant}/api/auth/me'
*/
me.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: me.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Authentication\TenantAuthController::me
* @see app/Http/Controllers/Tenant/API/Auth/Authentication/TenantAuthController.php:12
* @route 'https://app.tito.ai/{tenant}/api/auth/me'
*/
const meForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Authentication\TenantAuthController::me
* @see app/Http/Controllers/Tenant/API/Auth/Authentication/TenantAuthController.php:12
* @route 'https://app.tito.ai/{tenant}/api/auth/me'
*/
meForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Authentication\TenantAuthController::me
* @see app/Http/Controllers/Tenant/API/Auth/Authentication/TenantAuthController.php:12
* @route 'https://app.tito.ai/{tenant}/api/auth/me'
*/
meForm.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

me.form = meForm

const TenantAuthController = { me }

export default TenantAuthController