import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:21
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/invitations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:21
* @route 'https://app.tito.ai/{tenant}/api/invitations'
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
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:21
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:21
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:21
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
const indexForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:21
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
indexForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::index
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:21
* @route 'https://app.tito.ai/{tenant}/api/invitations'
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

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::store
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:34
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
export const store = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/invitations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::store
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:34
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
store.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return store.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::store
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:34
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
store.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::store
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:34
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
const storeForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::store
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:34
* @route 'https://app.tito.ai/{tenant}/api/invitations'
*/
storeForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::storeBatch
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:49
* @route 'https://app.tito.ai/{tenant}/api/invitations/batch'
*/
export const storeBatch = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeBatch.url(args, options),
    method: 'post',
})

storeBatch.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/invitations/batch',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::storeBatch
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:49
* @route 'https://app.tito.ai/{tenant}/api/invitations/batch'
*/
storeBatch.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return storeBatch.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::storeBatch
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:49
* @route 'https://app.tito.ai/{tenant}/api/invitations/batch'
*/
storeBatch.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: storeBatch.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::storeBatch
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:49
* @route 'https://app.tito.ai/{tenant}/api/invitations/batch'
*/
const storeBatchForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeBatch.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::storeBatch
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:49
* @route 'https://app.tito.ai/{tenant}/api/invitations/batch'
*/
storeBatchForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: storeBatch.url(args, options),
    method: 'post',
})

storeBatch.form = storeBatchForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::reinvite
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:67
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/reinvite'
*/
export const reinvite = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reinvite.url(args, options),
    method: 'post',
})

reinvite.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/reinvite',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::reinvite
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:67
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/reinvite'
*/
reinvite.url = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            invitation: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        invitation: typeof args.invitation === 'object'
        ? args.invitation.id
        : args.invitation,
    }

    return reinvite.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{invitation}', parsedArgs.invitation.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::reinvite
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:67
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/reinvite'
*/
reinvite.post = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: reinvite.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::reinvite
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:67
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/reinvite'
*/
const reinviteForm = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reinvite.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::reinvite
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:67
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/reinvite'
*/
reinviteForm.post = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: reinvite.url(args, options),
    method: 'post',
})

reinvite.form = reinviteForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::resend
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:82
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/resend'
*/
export const resend = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resend.url(args, options),
    method: 'post',
})

resend.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/resend',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::resend
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:82
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/resend'
*/
resend.url = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            invitation: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        invitation: typeof args.invitation === 'object'
        ? args.invitation.id
        : args.invitation,
    }

    return resend.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{invitation}', parsedArgs.invitation.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::resend
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:82
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/resend'
*/
resend.post = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resend.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::resend
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:82
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/resend'
*/
const resendForm = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resend.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::resend
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:82
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}/resend'
*/
resendForm.post = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resend.url(args, options),
    method: 'post',
})

resend.form = resendForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::revoke
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:94
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}'
*/
export const revoke = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revoke.url(args, options),
    method: 'delete',
})

revoke.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/invitations/{invitation}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::revoke
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:94
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}'
*/
revoke.url = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            invitation: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        invitation: typeof args.invitation === 'object'
        ? args.invitation.id
        : args.invitation,
    }

    return revoke.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{invitation}', parsedArgs.invitation.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::revoke
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:94
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}'
*/
revoke.delete = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revoke.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::revoke
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:94
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}'
*/
const revokeForm = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revoke.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Invitation\InvitationController::revoke
* @see app/Http/Controllers/Tenant/API/Auth/Invitation/InvitationController.php:94
* @route 'https://app.tito.ai/{tenant}/api/invitations/{invitation}'
*/
revokeForm.delete = (args: { tenant: string | number, invitation: string | { id: string } } | [tenant: string | number, invitation: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revoke.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

revoke.form = revokeForm

const InvitationController = { index, store, storeBatch, reinvite, resend, revoke }

export default InvitationController