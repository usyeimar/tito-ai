import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::forgotPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:20
* @route 'https://app.tito.ai/api/auth/forgot-password'
*/
export const forgotPassword = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: forgotPassword.url(options),
    method: 'post',
})

forgotPassword.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/forgot-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::forgotPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:20
* @route 'https://app.tito.ai/api/auth/forgot-password'
*/
forgotPassword.url = (options?: RouteQueryOptions) => {
    return forgotPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::forgotPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:20
* @route 'https://app.tito.ai/api/auth/forgot-password'
*/
forgotPassword.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: forgotPassword.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::forgotPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:20
* @route 'https://app.tito.ai/api/auth/forgot-password'
*/
const forgotPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: forgotPassword.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::forgotPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:20
* @route 'https://app.tito.ai/api/auth/forgot-password'
*/
forgotPasswordForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: forgotPassword.url(options),
    method: 'post',
})

forgotPassword.form = forgotPasswordForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::resetPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:27
* @route 'https://app.tito.ai/api/auth/reset-password'
*/
export const resetPassword = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetPassword.url(options),
    method: 'post',
})

resetPassword.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/reset-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::resetPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:27
* @route 'https://app.tito.ai/api/auth/reset-password'
*/
resetPassword.url = (options?: RouteQueryOptions) => {
    return resetPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::resetPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:27
* @route 'https://app.tito.ai/api/auth/reset-password'
*/
resetPassword.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: resetPassword.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::resetPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:27
* @route 'https://app.tito.ai/api/auth/reset-password'
*/
const resetPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetPassword.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::resetPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:27
* @route 'https://app.tito.ai/api/auth/reset-password'
*/
resetPasswordForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: resetPassword.url(options),
    method: 'post',
})

resetPassword.form = resetPasswordForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::confirmPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:34
* @route 'https://app.tito.ai/api/auth/confirm-password'
*/
export const confirmPassword = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmPassword.url(options),
    method: 'post',
})

confirmPassword.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/confirm-password',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::confirmPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:34
* @route 'https://app.tito.ai/api/auth/confirm-password'
*/
confirmPassword.url = (options?: RouteQueryOptions) => {
    return confirmPassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::confirmPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:34
* @route 'https://app.tito.ai/api/auth/confirm-password'
*/
confirmPassword.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirmPassword.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::confirmPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:34
* @route 'https://app.tito.ai/api/auth/confirm-password'
*/
const confirmPasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: confirmPassword.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Password\PasswordController::confirmPassword
* @see app/Http/Controllers/Central/API/Auth/Password/PasswordController.php:34
* @route 'https://app.tito.ai/api/auth/confirm-password'
*/
confirmPasswordForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: confirmPassword.url(options),
    method: 'post',
})

confirmPassword.form = confirmPasswordForm

const PasswordController = { forgotPassword, resetPassword, confirmPassword }

export default PasswordController