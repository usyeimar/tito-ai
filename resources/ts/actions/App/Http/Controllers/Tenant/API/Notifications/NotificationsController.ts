import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::index
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:17
* @route 'https://app.tito.ai/{tenant}/api/notifications'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/notifications',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::index
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:17
* @route 'https://app.tito.ai/{tenant}/api/notifications'
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
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::index
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:17
* @route 'https://app.tito.ai/{tenant}/api/notifications'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::index
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:17
* @route 'https://app.tito.ai/{tenant}/api/notifications'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::index
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:17
* @route 'https://app.tito.ai/{tenant}/api/notifications'
*/
const indexForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::index
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:17
* @route 'https://app.tito.ai/{tenant}/api/notifications'
*/
indexForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::index
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:17
* @route 'https://app.tito.ai/{tenant}/api/notifications'
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
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchMarkRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:46
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/read'
*/
export const batchMarkRead = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: batchMarkRead.url(args, options),
    method: 'post',
})

batchMarkRead.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/notifications/batch/read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchMarkRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:46
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/read'
*/
batchMarkRead.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return batchMarkRead.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchMarkRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:46
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/read'
*/
batchMarkRead.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: batchMarkRead.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchMarkRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:46
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/read'
*/
const batchMarkReadForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: batchMarkRead.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchMarkRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:46
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/read'
*/
batchMarkReadForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: batchMarkRead.url(args, options),
    method: 'post',
})

batchMarkRead.form = batchMarkReadForm

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchDestroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:73
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/delete'
*/
export const batchDestroy = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: batchDestroy.url(args, options),
    method: 'post',
})

batchDestroy.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/notifications/batch/delete',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchDestroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:73
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/delete'
*/
batchDestroy.url = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return batchDestroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchDestroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:73
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/delete'
*/
batchDestroy.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: batchDestroy.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchDestroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:73
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/delete'
*/
const batchDestroyForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: batchDestroy.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::batchDestroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:73
* @route 'https://app.tito.ai/{tenant}/api/notifications/batch/delete'
*/
batchDestroyForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: batchDestroy.url(args, options),
    method: 'post',
})

batchDestroy.form = batchDestroyForm

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::markRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:35
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}/read'
*/
export const markRead = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markRead.url(args, options),
    method: 'post',
})

markRead.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/notifications/{notification}/read',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::markRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:35
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}/read'
*/
markRead.url = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            notification: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        notification: typeof args.notification === 'object'
        ? args.notification.id
        : args.notification,
    }

    return markRead.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{notification}', parsedArgs.notification.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::markRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:35
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}/read'
*/
markRead.post = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: markRead.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::markRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:35
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}/read'
*/
const markReadForm = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markRead.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::markRead
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:35
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}/read'
*/
markReadForm.post = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: markRead.url(args, options),
    method: 'post',
})

markRead.form = markReadForm

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::destroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:65
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}'
*/
export const destroy = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/notifications/{notification}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::destroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:65
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}'
*/
destroy.url = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            notification: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        notification: typeof args.notification === 'object'
        ? args.notification.id
        : args.notification,
    }

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{notification}', parsedArgs.notification.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::destroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:65
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}'
*/
destroy.delete = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::destroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:65
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}'
*/
const destroyForm = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Notifications\NotificationsController::destroy
* @see app/Http/Controllers/Tenant/API/Notifications/NotificationsController.php:65
* @route 'https://app.tito.ai/{tenant}/api/notifications/{notification}'
*/
destroyForm.delete = (args: { tenant: string | number, notification: string | number | { id: string | number } } | [tenant: string | number, notification: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const NotificationsController = { index, batchMarkRead, batchDestroy, markRead, destroy }

export default NotificationsController