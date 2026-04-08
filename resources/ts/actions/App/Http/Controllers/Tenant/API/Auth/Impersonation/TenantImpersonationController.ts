import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::start
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:22
* @route 'https://app.tito.ai/{tenant}/api/impersonate/{token}'
*/
export const start = (args: { tenant: string | number, token: string | number } | [tenant: string | number, token: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: start.url(args, options),
    method: 'post',
})

start.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/impersonate/{token}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::start
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:22
* @route 'https://app.tito.ai/{tenant}/api/impersonate/{token}'
*/
start.url = (args: { tenant: string | number, token: string | number } | [tenant: string | number, token: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            token: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        token: args.token,
    }

    return start.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::start
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:22
* @route 'https://app.tito.ai/{tenant}/api/impersonate/{token}'
*/
start.post = (args: { tenant: string | number, token: string | number } | [tenant: string | number, token: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: start.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::start
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:22
* @route 'https://app.tito.ai/{tenant}/api/impersonate/{token}'
*/
const startForm = (args: { tenant: string | number, token: string | number } | [tenant: string | number, token: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: start.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::start
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:22
* @route 'https://app.tito.ai/{tenant}/api/impersonate/{token}'
*/
startForm.post = (args: { tenant: string | number, token: string | number } | [tenant: string | number, token: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: start.url(args, options),
    method: 'post',
})

start.form = startForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::end
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:84
* @route 'https://app.tito.ai/{tenant}/api/impersonate/end'
*/
export const end = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: end.url(args, options),
    method: 'post',
})

end.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/impersonate/end',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::end
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:84
* @route 'https://app.tito.ai/{tenant}/api/impersonate/end'
*/
end.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return end.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::end
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:84
* @route 'https://app.tito.ai/{tenant}/api/impersonate/end'
*/
end.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: end.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::end
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:84
* @route 'https://app.tito.ai/{tenant}/api/impersonate/end'
*/
const endForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: end.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Impersonation\TenantImpersonationController::end
* @see app/Http/Controllers/Tenant/API/Auth/Impersonation/TenantImpersonationController.php:84
* @route 'https://app.tito.ai/{tenant}/api/impersonate/end'
*/
endForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: end.url(args, options),
    method: 'post',
})

end.form = endForm

const TenantImpersonationController = { start, end }

export default TenantImpersonationController