import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
export const authenticate = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticate.url(args, options),
    method: 'get',
})

authenticate.definition = {
    methods: ["get","post","head"],
    url: 'https://app.tito.ai/{tenant}/api/broadcasting/auth',
} satisfies RouteDefinition<["get","post","head"]>

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
authenticate.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return authenticate.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
authenticate.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: authenticate.url(args, options),
    method: 'get',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
authenticate.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: authenticate.url(args, options),
    method: 'post',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
authenticate.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: authenticate.url(args, options),
    method: 'head',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
const authenticateForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: authenticate.url(args, options),
    method: 'get',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
authenticateForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: authenticate.url(args, options),
    method: 'get',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
authenticateForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: authenticate.url(args, options),
    method: 'post',
})

/**
* @see \Illuminate\Broadcasting\BroadcastController::authenticate
* @see vendor/laravel/framework/src/Illuminate/Broadcasting/BroadcastController.php:18
* @route 'https://app.tito.ai/{tenant}/api/broadcasting/auth'
*/
authenticateForm.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: authenticate.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

authenticate.form = authenticateForm

const BroadcastController = { authenticate }

export default BroadcastController