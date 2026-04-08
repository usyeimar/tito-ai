import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::passkeys
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:16
* @route 'https://app.tito.ai/api/auth/passkeys'
*/
export const passkeys = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: passkeys.url(options),
    method: 'get',
})

passkeys.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/auth/passkeys',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::passkeys
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:16
* @route 'https://app.tito.ai/api/auth/passkeys'
*/
passkeys.url = (options?: RouteQueryOptions) => {
    return passkeys.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::passkeys
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:16
* @route 'https://app.tito.ai/api/auth/passkeys'
*/
passkeys.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: passkeys.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::passkeys
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:16
* @route 'https://app.tito.ai/api/auth/passkeys'
*/
passkeys.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: passkeys.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::passkeys
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:16
* @route 'https://app.tito.ai/api/auth/passkeys'
*/
const passkeysForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: passkeys.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::passkeys
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:16
* @route 'https://app.tito.ai/api/auth/passkeys'
*/
passkeysForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: passkeys.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::passkeys
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:16
* @route 'https://app.tito.ai/api/auth/passkeys'
*/
passkeysForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: passkeys.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

passkeys.form = passkeysForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::revokePasskey
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:23
* @route 'https://app.tito.ai/api/auth/passkeys/{credential}'
*/
export const revokePasskey = (args: { credential: string | number } | [credential: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokePasskey.url(args, options),
    method: 'delete',
})

revokePasskey.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/api/auth/passkeys/{credential}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::revokePasskey
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:23
* @route 'https://app.tito.ai/api/auth/passkeys/{credential}'
*/
revokePasskey.url = (args: { credential: string | number } | [credential: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { credential: args }
    }

    if (Array.isArray(args)) {
        args = {
            credential: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        credential: args.credential,
    }

    return revokePasskey.definition.url
            .replace('{credential}', parsedArgs.credential.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::revokePasskey
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:23
* @route 'https://app.tito.ai/api/auth/passkeys/{credential}'
*/
revokePasskey.delete = (args: { credential: string | number } | [credential: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokePasskey.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::revokePasskey
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:23
* @route 'https://app.tito.ai/api/auth/passkeys/{credential}'
*/
const revokePasskeyForm = (args: { credential: string | number } | [credential: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revokePasskey.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyController::revokePasskey
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyController.php:23
* @route 'https://app.tito.ai/api/auth/passkeys/{credential}'
*/
revokePasskeyForm.delete = (args: { credential: string | number } | [credential: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revokePasskey.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

revokePasskey.form = revokePasskeyForm

const PasskeyController = { passkeys, revokePasskey }

export default PasskeyController