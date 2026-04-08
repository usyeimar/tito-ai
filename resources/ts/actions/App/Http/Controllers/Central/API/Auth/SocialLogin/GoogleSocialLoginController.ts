import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\GoogleSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/GoogleSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/google'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/google',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\GoogleSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/GoogleSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/google'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\GoogleSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/GoogleSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/google'
*/
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\GoogleSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/GoogleSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/google'
*/
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\SocialLogin\GoogleSocialLoginController::login
* @see app/Http/Controllers/Central/API/Auth/SocialLogin/GoogleSocialLoginController.php:16
* @route 'https://app.tito.ai/api/auth/google'
*/
loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

login.form = loginForm

const GoogleSocialLoginController = { login }

export default GoogleSocialLoginController