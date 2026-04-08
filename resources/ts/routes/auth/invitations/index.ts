import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::resolve
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:30
* @route 'https://app.tito.ai/api/auth/invitations/resolve'
*/
export const resolve = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: resolve.url(options),
    method: 'get',
})

resolve.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/auth/invitations/resolve',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::resolve
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:30
* @route 'https://app.tito.ai/api/auth/invitations/resolve'
*/
resolve.url = (options?: RouteQueryOptions) => {
    return resolve.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::resolve
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:30
* @route 'https://app.tito.ai/api/auth/invitations/resolve'
*/
resolve.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: resolve.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::resolve
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:30
* @route 'https://app.tito.ai/api/auth/invitations/resolve'
*/
resolve.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: resolve.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::resolve
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:30
* @route 'https://app.tito.ai/api/auth/invitations/resolve'
*/
const resolveForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: resolve.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::resolve
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:30
* @route 'https://app.tito.ai/api/auth/invitations/resolve'
*/
resolveForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: resolve.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::resolve
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:30
* @route 'https://app.tito.ai/api/auth/invitations/resolve'
*/
resolveForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: resolve.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

resolve.form = resolveForm

const invitations = {
    resolve: Object.assign(resolve, resolve),
}

export default invitations