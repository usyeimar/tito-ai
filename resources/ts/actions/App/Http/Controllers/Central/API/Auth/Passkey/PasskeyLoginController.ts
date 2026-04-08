import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::getOptions
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/login/options'
*/
export const getOptions = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: getOptions.url(options),
    method: 'post',
})

getOptions.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/passkeys/login/options',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::getOptions
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/login/options'
*/
getOptions.url = (options?: RouteQueryOptions) => {
    return getOptions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::getOptions
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/login/options'
*/
getOptions.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: getOptions.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::getOptions
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/login/options'
*/
const getOptionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: getOptions.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::getOptions
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/login/options'
*/
getOptionsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: getOptions.url(options),
    method: 'post',
})

getOptions.form = getOptionsForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::login
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:29
* @route 'https://app.tito.ai/api/auth/passkeys/login'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/passkeys/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::login
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:29
* @route 'https://app.tito.ai/api/auth/passkeys/login'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::login
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:29
* @route 'https://app.tito.ai/api/auth/passkeys/login'
*/
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::login
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:29
* @route 'https://app.tito.ai/api/auth/passkeys/login'
*/
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyLoginController::login
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController.php:29
* @route 'https://app.tito.ai/api/auth/passkeys/login'
*/
loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

login.form = loginForm

const PasskeyLoginController = { getOptions, login }

export default PasskeyLoginController