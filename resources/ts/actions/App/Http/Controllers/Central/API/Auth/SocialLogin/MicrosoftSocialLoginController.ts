import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\MicrosoftSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/MicrosoftSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/microsoft'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/microsoft',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\MicrosoftSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/MicrosoftSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/microsoft'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\MicrosoftSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/MicrosoftSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/microsoft'
*/
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\MicrosoftSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/MicrosoftSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/microsoft'
*/
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\MicrosoftSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/MicrosoftSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/microsoft'
*/
loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

login.form = loginForm

const MicrosoftSocialLoginController = { login }

export default MicrosoftSocialLoginController