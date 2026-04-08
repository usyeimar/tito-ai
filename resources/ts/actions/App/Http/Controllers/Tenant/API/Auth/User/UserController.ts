import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::index
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:25
* @route 'https://app.tito.ai/{tenant}/api/users'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/users',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::index
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:25
* @route 'https://app.tito.ai/{tenant}/api/users'
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
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::index
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:25
* @route 'https://app.tito.ai/{tenant}/api/users'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::index
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:25
* @route 'https://app.tito.ai/{tenant}/api/users'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::index
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:25
* @route 'https://app.tito.ai/{tenant}/api/users'
*/
const indexForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::index
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:25
* @route 'https://app.tito.ai/{tenant}/api/users'
*/
indexForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::index
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:25
* @route 'https://app.tito.ai/{tenant}/api/users'
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
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::show
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:46
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
export const show = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/users/{user}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::show
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:46
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
show.url = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            user: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::show
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:46
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
show.get = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::show
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:46
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
show.head = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::show
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:46
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
const showForm = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::show
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:46
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
showForm.get = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::show
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:46
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
showForm.head = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::update
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:51
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
export const update = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/{tenant}/api/users/{user}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::update
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:51
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
update.url = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            user: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return update.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::update
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:51
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
update.patch = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::update
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:51
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
const updateForm = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::update
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:51
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
updateForm.patch = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::updatePassword
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:62
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/password'
*/
export const updatePassword = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePassword.url(args, options),
    method: 'patch',
})

updatePassword.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/{tenant}/api/users/{user}/password',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::updatePassword
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:62
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/password'
*/
updatePassword.url = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            user: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return updatePassword.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::updatePassword
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:62
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/password'
*/
updatePassword.patch = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePassword.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::updatePassword
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:62
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/password'
*/
const updatePasswordForm = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePassword.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::updatePassword
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:62
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/password'
*/
updatePasswordForm.patch = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePassword.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updatePassword.form = updatePasswordForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:74
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
export const destroy = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/users/{user}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:74
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
destroy.url = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            user: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:74
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
destroy.delete = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:74
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
const destroyForm = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::destroy
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:74
* @route 'https://app.tito.ai/{tenant}/api/users/{user}'
*/
destroyForm.delete = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::assignRoles
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:85
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles'
*/
export const assignRoles = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assignRoles.url(args, options),
    method: 'post',
})

assignRoles.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/users/{user}/roles',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::assignRoles
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:85
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles'
*/
assignRoles.url = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            user: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return assignRoles.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::assignRoles
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:85
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles'
*/
assignRoles.post = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: assignRoles.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::assignRoles
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:85
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles'
*/
const assignRolesForm = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: assignRoles.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::assignRoles
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:85
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles'
*/
assignRolesForm.post = (args: { tenant: string | number, user: string | { id: string } } | [tenant: string | number, user: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: assignRoles.url(args, options),
    method: 'post',
})

assignRoles.form = assignRolesForm

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::revokeRole
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:98
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles/{role}'
*/
export const revokeRole = (args: { tenant: string | number, user: string | { id: string }, role: string | { id: string } } | [tenant: string | number, user: string | { id: string }, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokeRole.url(args, options),
    method: 'delete',
})

revokeRole.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/users/{user}/roles/{role}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::revokeRole
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:98
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles/{role}'
*/
revokeRole.url = (args: { tenant: string | number, user: string | { id: string }, role: string | { id: string } } | [tenant: string | number, user: string | { id: string }, role: string | { id: string } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            user: args[1],
            role: args[2],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
        role: typeof args.role === 'object'
        ? args.role.id
        : args.role,
    }

    return revokeRole.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{user}', parsedArgs.user.toString())
            .replace('{role}', parsedArgs.role.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::revokeRole
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:98
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles/{role}'
*/
revokeRole.delete = (args: { tenant: string | number, user: string | { id: string }, role: string | { id: string } } | [tenant: string | number, user: string | { id: string }, role: string | { id: string } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: revokeRole.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::revokeRole
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:98
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles/{role}'
*/
const revokeRoleForm = (args: { tenant: string | number, user: string | { id: string }, role: string | { id: string } } | [tenant: string | number, user: string | { id: string }, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revokeRole.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Auth\User\UserController::revokeRole
* @see app/Http/Controllers/Tenant/API/Auth/User/UserController.php:98
* @route 'https://app.tito.ai/{tenant}/api/users/{user}/roles/{role}'
*/
revokeRoleForm.delete = (args: { tenant: string | number, user: string | { id: string }, role: string | { id: string } } | [tenant: string | number, user: string | { id: string }, role: string | { id: string } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: revokeRole.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

revokeRole.form = revokeRoleForm

const UserController = { index, show, update, updatePassword, destroy, assignRoles, revokeRole }

export default UserController