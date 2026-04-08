import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults, validateParameters } from './../../../../wayfinder'
/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/tenancy/assets/{path?}'
*/
const TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30 = (args?: { path?: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.url(args, options),
    method: 'get',
})

TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/tenancy/assets/{path?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/tenancy/assets/{path?}'
*/
TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.url = (args?: { path?: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { path: args }
    }

    if (Array.isArray(args)) {
        args = {
            path: args[0],
        }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
        "path",
    ])

    const parsedArgs = {
        path: args?.path,
    }

    return TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.definition.url
            .replace('{path?}', parsedArgs.path?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/tenancy/assets/{path?}'
*/
TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.get = (args?: { path?: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/tenancy/assets/{path?}'
*/
TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.head = (args?: { path?: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.url(args, options),
    method: 'head',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/tenancy/assets/{path?}'
*/
const TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30Form = (args?: { path?: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/tenancy/assets/{path?}'
*/
TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30Form.get = (args?: { path?: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/tenancy/assets/{path?}'
*/
TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30Form.head = (args?: { path?: string | number } | [path: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30.form = TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30Form
/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
const TenantAssetController616a2b281f21b1e16cde54ad8522d5f1 = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.url(args, options),
    method: 'get',
})

TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.url = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            path: args[1],
        }
    }

    args = applyUrlDefaults(args)

    validateParameters(args, [
        "path",
    ])

    const parsedArgs = {
        tenant: args.tenant,
        path: args.path,
    }

    return TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{path?}', parsedArgs.path?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.get = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.head = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.url(args, options),
    method: 'head',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
const TenantAssetController616a2b281f21b1e16cde54ad8522d5f1Form = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
TenantAssetController616a2b281f21b1e16cde54ad8522d5f1Form.get = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
TenantAssetController616a2b281f21b1e16cde54ad8522d5f1Form.head = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

TenantAssetController616a2b281f21b1e16cde54ad8522d5f1.form = TenantAssetController616a2b281f21b1e16cde54ad8522d5f1Form

const TenantAssetController = {
    'https://app.tito.ai/tenancy/assets/{path?}': TenantAssetController3cb908d85b888f832fdcaa0da3bd1e30,
    'https://app.tito.ai/{tenant}/tenancy/assets/{path?}': TenantAssetController616a2b281f21b1e16cde54ad8522d5f1,
}

export default TenantAssetController