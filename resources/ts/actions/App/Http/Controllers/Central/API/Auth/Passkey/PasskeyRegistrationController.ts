import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::options
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/register/options'
*/
export const options = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: options.url(options),
    method: 'post',
})

options.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/passkeys/register/options',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::options
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/register/options'
*/
options.url = (options?: RouteQueryOptions) => {
    return options.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::options
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/register/options'
*/
options.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: options.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::options
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/register/options'
*/
const optionsForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: options.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::options
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:18
* @route 'https://app.tito.ai/api/auth/passkeys/register/options'
*/
optionsForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: options.url(options),
    method: 'post',
})

options.form = optionsForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::store
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:31
* @route 'https://app.tito.ai/api/auth/passkeys/register'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/passkeys/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::store
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:31
* @route 'https://app.tito.ai/api/auth/passkeys/register'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::store
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:31
* @route 'https://app.tito.ai/api/auth/passkeys/register'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::store
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:31
* @route 'https://app.tito.ai/api/auth/passkeys/register'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Passkey\PasskeyRegistrationController::store
* @see app/Http/Controllers/Central/API/Auth/Passkey/PasskeyRegistrationController.php:31
* @route 'https://app.tito.ai/api/auth/passkeys/register'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

const PasskeyRegistrationController = { options, store }

export default PasskeyRegistrationController