import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::index
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:22
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/system/column-configurations',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::index
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:22
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
index.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tenant: args }
    }

    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
    }

    return index.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::index
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:22
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::index
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:22
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::index
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:22
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
const indexForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::index
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:22
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
indexForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::index
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:22
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
indexForm.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::store
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:29
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
export const store = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/system/column-configurations',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::store
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:29
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
store.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { tenant: args }
    }

    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
    }

    return store.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::store
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:29
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
store.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::store
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:29
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
const storeForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::store
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:29
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations'
*/
storeForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::show
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:38
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
export const show = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::show
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:38
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
show.url = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            systemColumnConfiguration: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        systemColumnConfiguration: typeof args.systemColumnConfiguration === 'object'
        ? args.systemColumnConfiguration.id
        : args.systemColumnConfiguration,
    }

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{systemColumnConfiguration}', parsedArgs.systemColumnConfiguration.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::show
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:38
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
show.get = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::show
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:38
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
show.head = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::show
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:38
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
const showForm = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::show
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:38
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
showForm.get = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::show
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:38
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
showForm.head = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::update
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:45
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
export const update = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::update
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:45
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
update.url = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            systemColumnConfiguration: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        systemColumnConfiguration: typeof args.systemColumnConfiguration === 'object'
        ? args.systemColumnConfiguration.id
        : args.systemColumnConfiguration,
    }

    return update.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{systemColumnConfiguration}', parsedArgs.systemColumnConfiguration.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::update
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:45
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
update.put = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::update
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:45
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
update.patch = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::update
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:45
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
const updateForm = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::update
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:45
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
updateForm.put = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::update
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:45
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
updateForm.patch = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::destroy
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:52
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
export const destroy = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::destroy
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:52
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
destroy.url = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            systemColumnConfiguration: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        systemColumnConfiguration: typeof args.systemColumnConfiguration === 'object'
        ? args.systemColumnConfiguration.id
        : args.systemColumnConfiguration,
    }

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{systemColumnConfiguration}', parsedArgs.systemColumnConfiguration.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::destroy
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:52
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
destroy.delete = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::destroy
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:52
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
const destroyForm = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\System\ColumnConfiguration\SystemColumnConfigurationsController::destroy
* @see app/Http/Controllers/Tenant/API/System/ColumnConfiguration/SystemColumnConfigurationsController.php:52
* @route 'https://app.tito.ai/{tenant}/api/system/column-configurations/{systemColumnConfiguration}'
*/
destroyForm.delete = (args: { tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } } | [tenant: string | number, systemColumnConfiguration: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const SystemColumnConfigurationsController = { index, store, show, update, destroy }

export default SystemColumnConfigurationsController