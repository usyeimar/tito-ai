import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Tenant/API/Auth/Token/TokenController.php:22
* @route 'https://app.tito.ai/{tenant}/api/refresh'
*/
export const refresh = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refresh.url(args, options),
    method: 'post',
})

refresh.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/refresh',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Tenant/API/Auth/Token/TokenController.php:22
* @route 'https://app.tito.ai/{tenant}/api/refresh'
*/
refresh.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return refresh.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Tenant/API/Auth/Token/TokenController.php:22
* @route 'https://app.tito.ai/{tenant}/api/refresh'
*/
refresh.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refresh.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Tenant/API/Auth/Token/TokenController.php:22
* @route 'https://app.tito.ai/{tenant}/api/refresh'
*/
const refreshForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: refresh.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Tenant/API/Auth/Token/TokenController.php:22
* @route 'https://app.tito.ai/{tenant}/api/refresh'
*/
refreshForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: refresh.url(args, options),
    method: 'post',
})

refresh.form = refreshForm

const TokenController = { refresh }

export default TokenController