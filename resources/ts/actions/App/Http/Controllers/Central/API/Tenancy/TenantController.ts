import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::index
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:23
* @route 'https://app.tito.ai/api/tenants'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/tenants',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::index
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:23
* @route 'https://app.tito.ai/api/tenants'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::index
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:23
* @route 'https://app.tito.ai/api/tenants'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::index
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:23
* @route 'https://app.tito.ai/api/tenants'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::index
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:23
* @route 'https://app.tito.ai/api/tenants'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::index
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:23
* @route 'https://app.tito.ai/api/tenants'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::index
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:23
* @route 'https://app.tito.ai/api/tenants'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::store
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:44
* @route 'https://app.tito.ai/api/tenants'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/tenants',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::store
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:44
* @route 'https://app.tito.ai/api/tenants'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::store
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:44
* @route 'https://app.tito.ai/api/tenants'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::store
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:44
* @route 'https://app.tito.ai/api/tenants'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::store
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:44
* @route 'https://app.tito.ai/api/tenants'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::show
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:56
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
export const show = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/tenants/{tenant}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::show
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:56
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
show.url = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tenant: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
        args = { tenant: args.slug }
    }

    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: typeof args.tenant === 'object'
        ? args.tenant.slug
        : args.tenant,
    }

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::show
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:56
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
show.get = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::show
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:56
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
show.head = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::show
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:56
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
const showForm = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::show
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:56
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
showForm.get = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::show
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:56
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
showForm.head = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::update
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:63
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
export const update = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/api/tenants/{tenant}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::update
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:63
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
update.url = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tenant: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
        args = { tenant: args.slug }
    }

    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: typeof args.tenant === 'object'
        ? args.tenant.slug
        : args.tenant,
    }

    return update.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::update
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:63
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
update.patch = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::update
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:63
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
const updateForm = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::update
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:63
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
updateForm.patch = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::destroy
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:74
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
export const destroy = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/api/tenants/{tenant}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::destroy
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:74
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
destroy.url = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tenant: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'slug' in args) {
        args = { tenant: args.slug }
    }

    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: typeof args.tenant === 'object'
        ? args.tenant.slug
        : args.tenant,
    }

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::destroy
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:74
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
destroy.delete = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::destroy
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:74
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
const destroyForm = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Tenancy\TenantController::destroy
* @see app/Http/Controllers/Central/API/Tenancy/TenantController.php:74
* @route 'https://app.tito.ai/api/tenants/{tenant}'
*/
destroyForm.delete = (args: { tenant: string | { slug: string } } | [tenant: string | { slug: string } ] | string | { slug: string }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const TenantController = { index, store, show, update, destroy }

export default TenantController