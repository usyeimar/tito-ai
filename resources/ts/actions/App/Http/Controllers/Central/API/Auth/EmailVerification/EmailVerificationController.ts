import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::resendEmailVerification
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:16
* @route 'https://app.tito.ai/api/auth/email/verification-notification'
*/
export const resendEmailVerification = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resendEmailVerification.url(options),
    method: 'post',
})

resendEmailVerification.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/email/verification-notification',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::resendEmailVerification
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:16
* @route 'https://app.tito.ai/api/auth/email/verification-notification'
*/
resendEmailVerification.url = (options?: RouteQueryOptions) => {
    return resendEmailVerification.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::resendEmailVerification
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:16
* @route 'https://app.tito.ai/api/auth/email/verification-notification'
*/
resendEmailVerification.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resendEmailVerification.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::resendEmailVerification
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:16
* @route 'https://app.tito.ai/api/auth/email/verification-notification'
*/
const resendEmailVerificationForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resendEmailVerification.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::resendEmailVerification
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:16
* @route 'https://app.tito.ai/api/auth/email/verification-notification'
*/
resendEmailVerificationForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resendEmailVerification.url(options),
    method: 'post',
})

resendEmailVerification.form = resendEmailVerificationForm

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verifyEmail
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
export const verifyEmail = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verifyEmail.url(args, options),
    method: 'get',
})

verifyEmail.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verifyEmail
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verifyEmail.url = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions) => {
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

    return verifyEmail.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace('{hash}', parsedArgs.hash.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verifyEmail
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verifyEmail.get = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: verifyEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verifyEmail
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verifyEmail.head = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: verifyEmail.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verifyEmail
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
const verifyEmailForm = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verifyEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verifyEmail
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verifyEmailForm.get = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verifyEmail.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\EmailVerification\EmailVerificationController::verifyEmail
* @see app/Http/Controllers/Central/API/Auth/EmailVerification/EmailVerificationController.php:34
* @route 'https://app.tito.ai/api/auth/email/verify/{id}/{hash}'
*/
verifyEmailForm.head = (args: { id: string | number, hash: string | number } | [id: string | number, hash: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: verifyEmail.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

verifyEmail.form = verifyEmailForm

const EmailVerificationController = { resendEmailVerification, verifyEmail }

export default EmailVerificationController