import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::tokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:49
* @route 'https://app.tito.ai/api/auth/tokens'
*/
export const tokens = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tokens.url(options),
    method: 'get',
})

tokens.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/auth/tokens',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::tokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:49
* @route 'https://app.tito.ai/api/auth/tokens'
*/
tokens.url = (options?: RouteQueryOptions) => {
    return tokens.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::tokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:49
* @route 'https://app.tito.ai/api/auth/tokens'
*/
tokens.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tokens.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::tokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:49
* @route 'https://app.tito.ai/api/auth/tokens'
*/
tokens.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tokens.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::tokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:49
* @route 'https://app.tito.ai/api/auth/tokens'
*/
const tokensForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: tokens.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::tokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:49
* @route 'https://app.tito.ai/api/auth/tokens'
*/
tokensForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: tokens.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::tokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:49
* @route 'https://app.tito.ai/api/auth/tokens'
*/
tokensForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: tokens.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

tokens.form = tokensForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeTokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:56
* @route 'https://app.tito.ai/api/auth/tokens'
*/
export const revokeTokens = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokeTokens.url(options),
    method: 'delete',
})

revokeTokens.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/api/auth/tokens',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeTokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:56
* @route 'https://app.tito.ai/api/auth/tokens'
*/
revokeTokens.url = (options?: RouteQueryOptions) => {
    return revokeTokens.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeTokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:56
* @route 'https://app.tito.ai/api/auth/tokens'
*/
revokeTokens.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokeTokens.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeTokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:56
* @route 'https://app.tito.ai/api/auth/tokens'
*/
const revokeTokensForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revokeTokens.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeTokens
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:56
* @route 'https://app.tito.ai/api/auth/tokens'
*/
revokeTokensForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revokeTokens.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

revokeTokens.form = revokeTokensForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeToken
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:63
* @route 'https://app.tito.ai/api/auth/tokens/{token}'
*/
export const revokeToken = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokeToken.url(args, options),
    method: 'delete',
})

revokeToken.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/api/auth/tokens/{token}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeToken
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:63
* @route 'https://app.tito.ai/api/auth/tokens/{token}'
*/
revokeToken.url = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { token: args }
    }

    if (Array.isArray(args)) {
        args = {
            token: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        token: args.token,
    }

    return revokeToken.definition.url
            .replace('{token}', parsedArgs.token.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeToken
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:63
* @route 'https://app.tito.ai/api/auth/tokens/{token}'
*/
revokeToken.delete = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokeToken.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeToken
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:63
* @route 'https://app.tito.ai/api/auth/tokens/{token}'
*/
const revokeTokenForm = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revokeToken.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::revokeToken
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:63
* @route 'https://app.tito.ai/api/auth/tokens/{token}'
*/
revokeTokenForm.delete = (args: { token: string | number } | [token: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revokeToken.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

revokeToken.form = revokeTokenForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:19
* @route 'https://app.tito.ai/api/auth/refresh'
*/
export const refresh = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refresh.url(options),
    method: 'post',
})

refresh.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/refresh',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:19
* @route 'https://app.tito.ai/api/auth/refresh'
*/
refresh.url = (options?: RouteQueryOptions) => {
    return refresh.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:19
* @route 'https://app.tito.ai/api/auth/refresh'
*/
refresh.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: refresh.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:19
* @route 'https://app.tito.ai/api/auth/refresh'
*/
const refreshForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: refresh.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Token\TokenController::refresh
* @see app/Http/Controllers/Central/API/Auth/Token/TokenController.php:19
* @route 'https://app.tito.ai/api/auth/refresh'
*/
refreshForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: refresh.url(options),
    method: 'post',
})

refresh.form = refreshForm

const TokenController = { tokens, revokeTokens, revokeToken, refresh }

export default TokenController