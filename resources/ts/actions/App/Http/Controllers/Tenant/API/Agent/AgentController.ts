import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::index
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
export const index = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/ai/agents',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::index
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
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
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::index
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
index.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::index
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
index.head = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::index
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
const indexForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::index
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
indexForm.get = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::index
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:19
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
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
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::store
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:26
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
export const store = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/{tenant}/api/ai/agents',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::store
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:26
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
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
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::store
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:26
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
store.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::store
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:26
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
const storeForm = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::store
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:26
* @route 'https://app.tito.ai/{tenant}/api/ai/agents'
*/
storeForm.post = (args: { tenant: string | number } | [tenant: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(args, options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::show
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
export const show = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::show
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
show.url = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            agent: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        agent: typeof args.agent === 'object'
        ? args.agent.id
        : args.agent,
    }

    return show.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{agent}', parsedArgs.agent.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::show
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
show.get = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::show
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
show.head = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::show
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
const showForm = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::show
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
showForm.get = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::show
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:38
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
showForm.head = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::update
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:49
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
export const update = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::update
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:49
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
update.url = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            agent: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        agent: typeof args.agent === 'object'
        ? args.agent.id
        : args.agent,
    }

    return update.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{agent}', parsedArgs.agent.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::update
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:49
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
update.patch = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::update
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:49
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
const updateForm = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::update
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:49
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
updateForm.patch = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::destroy
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:61
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
export const destroy = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::destroy
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:61
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
destroy.url = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            agent: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        agent: typeof args.agent === 'object'
        ? args.agent.id
        : args.agent,
    }

    return destroy.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{agent}', parsedArgs.agent.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::destroy
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:61
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
destroy.delete = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::destroy
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:61
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
const destroyForm = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\API\Agent\AgentController::destroy
* @see app/Http/Controllers/Tenant/API/Agent/AgentController.php:61
* @route 'https://app.tito.ai/{tenant}/api/ai/agents/{agent}'
*/
destroyForm.delete = (args: { tenant: string | number, agent: string | number | { id: string | number } } | [tenant: string | number, agent: string | number | { id: string | number } ], options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const AgentController = { index, store, show, update, destroy }

export default AgentController