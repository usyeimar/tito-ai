import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
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

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:19
* @route 'https://app.tito.ai/api/auth/invitations'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/auth/invitations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:19
* @route 'https://app.tito.ai/api/auth/invitations'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:19
* @route 'https://app.tito.ai/api/auth/invitations'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:19
* @route 'https://app.tito.ai/api/auth/invitations'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:19
* @route 'https://app.tito.ai/api/auth/invitations'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:19
* @route 'https://app.tito.ai/api/auth/invitations'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:19
* @route 'https://app.tito.ai/api/auth/invitations'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::accept
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:42
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/accept'
*/
export const accept = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: accept.url(args, options),
    method: 'post',
})

accept.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/invitations/{invitation}/accept',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::accept
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:42
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/accept'
*/
accept.url = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invitation: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { invitation: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            invitation: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        invitation: typeof args.invitation === 'object'
        ? args.invitation.id
        : args.invitation,
    }

    return accept.definition.url
            .replace('{invitation}', parsedArgs.invitation.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::accept
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:42
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/accept'
*/
accept.post = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: accept.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::accept
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:42
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/accept'
*/
const acceptForm = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: accept.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::accept
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:42
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/accept'
*/
acceptForm.post = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: accept.url(args, options),
    method: 'post',
})

accept.form = acceptForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::decline
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:50
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/decline'
*/
export const decline = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: decline.url(args, options),
    method: 'post',
})

decline.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/invitations/{invitation}/decline',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::decline
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:50
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/decline'
*/
decline.url = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { invitation: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { invitation: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            invitation: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        invitation: typeof args.invitation === 'object'
        ? args.invitation.id
        : args.invitation,
    }

    return decline.definition.url
            .replace('{invitation}', parsedArgs.invitation.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::decline
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:50
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/decline'
*/
decline.post = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: decline.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::decline
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:50
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/decline'
*/
const declineForm = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: decline.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Invitation\InvitationController::decline
* @see app/Http/Controllers/Central/API/Auth/Invitation/InvitationController.php:50
* @route 'https://app.tito.ai/api/auth/invitations/{invitation}/decline'
*/
declineForm.post = (args: { invitation: string | { id: string } } | [invitation: string | { id: string } ] | string | { id: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: decline.url(args, options),
    method: 'post',
})

decline.form = declineForm

const InvitationController = { resolve, index, accept, decline }

export default InvitationController