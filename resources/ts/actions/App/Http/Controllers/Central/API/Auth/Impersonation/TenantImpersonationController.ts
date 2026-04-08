import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Impersonation\TenantImpersonationController::create
* @see app/Http/Controllers/Central/API/Auth/Impersonation/TenantImpersonationController.php:17
* @route 'https://app.tito.ai/api/auth/impersonate'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

create.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/impersonate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Impersonation\TenantImpersonationController::create
* @see app/Http/Controllers/Central/API/Auth/Impersonation/TenantImpersonationController.php:17
* @route 'https://app.tito.ai/api/auth/impersonate'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Impersonation\TenantImpersonationController::create
* @see app/Http/Controllers/Central/API/Auth/Impersonation/TenantImpersonationController.php:17
* @route 'https://app.tito.ai/api/auth/impersonate'
*/
create.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: create.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Impersonation\TenantImpersonationController::create
* @see app/Http/Controllers/Central/API/Auth/Impersonation/TenantImpersonationController.php:17
* @route 'https://app.tito.ai/api/auth/impersonate'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: create.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Impersonation\TenantImpersonationController::create
* @see app/Http/Controllers/Central/API/Auth/Impersonation/TenantImpersonationController.php:17
* @route 'https://app.tito.ai/api/auth/impersonate'
*/
createForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: create.url(options),
    method: 'post',
})

create.form = createForm

const TenantImpersonationController = { create }

export default TenantImpersonationController