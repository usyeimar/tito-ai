import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::create
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route 'https://app.tito.ai/login'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/login',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::create
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route 'https://app.tito.ai/login'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::create
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route 'https://app.tito.ai/login'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::create
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route 'https://app.tito.ai/login'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::create
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route 'https://app.tito.ai/login'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::create
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route 'https://app.tito.ai/login'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::create
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:47
* @route 'https://app.tito.ai/login'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route 'https://app.tito.ai/login'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/login',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route 'https://app.tito.ai/login'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route 'https://app.tito.ai/login'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route 'https://app.tito.ai/login'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::store
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:58
* @route 'https://app.tito.ai/login'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::destroy
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route 'https://app.tito.ai/logout'
*/
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroy.url(options),
    method: 'post',
})

destroy.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::destroy
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route 'https://app.tito.ai/logout'
*/
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::destroy
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route 'https://app.tito.ai/logout'
*/
destroy.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: destroy.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::destroy
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route 'https://app.tito.ai/logout'
*/
const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(options),
    method: 'post',
})

/**
* @see \Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::destroy
* @see vendor/laravel/fortify/src/Http/Controllers/AuthenticatedSessionController.php:100
* @route 'https://app.tito.ai/logout'
*/
destroyForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(options),
    method: 'post',
})

destroy.form = destroyForm

const AuthenticatedSessionController = { create, store, destroy }

export default AuthenticatedSessionController