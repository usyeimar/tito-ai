import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults, validateParameters } from './../../../../wayfinder'
/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
export const asset = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: asset.url(args, options),
    method: 'get',
})

asset.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
asset.url = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions) => {
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

    return asset.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{path?}', parsedArgs.path?.toString() ?? '')
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
asset.get = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: asset.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
asset.head = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: asset.url(args, options),
    method: 'head',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
const assetForm = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: asset.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
assetForm.get = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: asset.url(args, options),
    method: 'get',
})

/**
* @see \Stancl\Tenancy\Controllers\TenantAssetController::__invoke
* @see vendor/stancl/tenancy/src/Controllers/TenantAssetController.php:45
* @route 'https://app.tito.ai/{tenant}/tenancy/assets/{path?}'
*/
assetForm.head = (args: { tenant: string | number, path?: string | number } | [tenant: string | number, path: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: asset.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

asset.form = assetForm

const tenancy = {
    asset: Object.assign(asset, asset),
}

export default tenancy