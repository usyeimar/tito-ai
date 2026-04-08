import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verify
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
export const verify = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verify.url(args, options),
    method: 'get',
})

verify.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verify
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verify.url = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            id: args[0],
            hash: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        id: args.id,
        hash: args.hash,
    }

    return verify.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace('{hash}', parsedArgs.hash.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verify
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verify.get = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verify.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verify
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verify.head = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: verify.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verify
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
const verifyForm = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verify.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verify
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verifyForm.get = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verify.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verify
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verifyForm.head = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verify.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

verify.form = verifyForm

const verification = {
    verify: Object.assign(verify, verify),
}

export default verification