import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:23
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/roles',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:23
* @route 'https://app.tito.ai/{tenant}/api/roles'
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
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:23
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:23
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:23
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
const indexForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:23
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
indexForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::index
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:23
* @route 'https://app.tito.ai/{tenant}/api/roles'
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
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::store
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:44
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
export const store = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/roles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::store
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:44
* @route 'https://app.tito.ai/{tenant}/api/roles'
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
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::store
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:44
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
store.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::store
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:44
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
const storeForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::store
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:44
* @route 'https://app.tito.ai/{tenant}/api/roles'
*/
storeForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::show
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:54
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
export const show = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/roles/{role}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::show
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:54
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
show.url = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            role: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        role: typeof args.role === 'object'
        ? args.role.id
        : args.role,
    }

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{role}', parsedArgs.role.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::show
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:54
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
show.get = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::show
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:54
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
show.head = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::show
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:54
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
const showForm = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::show
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:54
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
showForm.get = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::show
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:54
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
showForm.head = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::update
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:61
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
export const update = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: 'https://app.tito.ai/{tenant}/api/roles/{role}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::update
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:61
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
update.url = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            role: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        role: typeof args.role === 'object'
        ? args.role.id
        : args.role,
    }

    return update.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{role}', parsedArgs.role.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::update
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:61
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
update.put = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::update
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:61
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
update.patch = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::update
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:61
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
const updateForm = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::update
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:61
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
updateForm.put = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::update
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:61
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
updateForm.patch = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:72
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
export const destroy = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/roles/{role}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:72
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
destroy.url = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            role: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        role: typeof args.role === 'object'
        ? args.role.id
        : args.role,
    }

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{role}', parsedArgs.role.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:72
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
destroy.delete = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:72
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
const destroyForm = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\Role\RoleController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/Role/RoleController.php:72
* @route 'https://app.tito.ai/{tenant}/api/roles/{role}'
*/
destroyForm.delete = (args: { tenant: string | number, role: string | { id: string } } | [tenant: string | number, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const RoleController = { index, store, show, update, destroy }

export default RoleController