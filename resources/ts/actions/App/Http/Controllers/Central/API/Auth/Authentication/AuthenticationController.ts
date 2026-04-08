import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::register
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:22
* @route 'https://app.tito.ai/api/auth/register'
*/
export const register = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

register.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/register',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::register
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:22
* @route 'https://app.tito.ai/api/auth/register'
*/
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::register
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:22
* @route 'https://app.tito.ai/api/auth/register'
*/
register.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: register.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::register
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:22
* @route 'https://app.tito.ai/api/auth/register'
*/
const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: register.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::register
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:22
* @route 'https://app.tito.ai/api/auth/register'
*/
registerForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: register.url(options),
    method: 'post',
})

register.form = registerForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::login
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:31
* @route 'https://app.tito.ai/api/auth/login'
*/
export const login = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

login.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::login
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:31
* @route 'https://app.tito.ai/api/auth/login'
*/
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::login
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:31
* @route 'https://app.tito.ai/api/auth/login'
*/
login.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::login
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:31
* @route 'https://app.tito.ai/api/auth/login'
*/
const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::login
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:31
* @route 'https://app.tito.ai/api/auth/login'
*/
loginForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: login.url(options),
    method: 'post',
})

login.form = loginForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::me
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:38
* @route 'https://app.tito.ai/api/auth/me'
*/
export const me = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})

me.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/auth/me',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::me
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:38
* @route 'https://app.tito.ai/api/auth/me'
*/
me.url = (options?: RouteQueryOptions) => {
    return me.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::me
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:38
* @route 'https://app.tito.ai/api/auth/me'
*/
me.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: me.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::me
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:38
* @route 'https://app.tito.ai/api/auth/me'
*/
me.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: me.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::me
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:38
* @route 'https://app.tito.ai/api/auth/me'
*/
const meForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::me
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:38
* @route 'https://app.tito.ai/api/auth/me'
*/
meForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::me
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:38
* @route 'https://app.tito.ai/api/auth/me'
*/
meForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: me.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

me.form = meForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::logout
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:53
* @route 'https://app.tito.ai/api/auth/logout'
*/
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::logout
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:53
* @route 'https://app.tito.ai/api/auth/logout'
*/
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::logout
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:53
* @route 'https://app.tito.ai/api/auth/logout'
*/
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::logout
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:53
* @route 'https://app.tito.ai/api/auth/logout'
*/
const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Authentication\AuthenticationController::logout
* @see app/Http/Controllers/Central/API/Auth/Authentication/AuthenticationController.php:53
* @route 'https://app.tito.ai/api/auth/logout'
*/
logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: logout.url(options),
    method: 'post',
})

logout.form = logoutForm

const AuthenticationController = { register, login, me, logout }

export default AuthenticationController