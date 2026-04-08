import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getWebWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
export const getWebWidgetConfig = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getWebWidgetConfig.url(args, options),
    method: 'get',
})

getWebWidgetConfig.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getWebWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
getWebWidgetConfig.url = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            agentSlug: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        agentSlug: args.agentSlug,
    }

    return getWebWidgetConfig.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{agentSlug}', parsedArgs.agentSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getWebWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
getWebWidgetConfig.get = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getWebWidgetConfig.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getWebWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
getWebWidgetConfig.head = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getWebWidgetConfig.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getWebWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
const getWebWidgetConfigForm = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getWebWidgetConfig.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getWebWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
getWebWidgetConfigForm.get = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getWebWidgetConfig.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getWebWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
getWebWidgetConfigForm.head = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getWebWidgetConfig.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

getWebWidgetConfig.form = getWebWidgetConfigForm

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getSipWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
export const getSipWidgetConfig = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getSipWidgetConfig.url(args, options),
    method: 'get',
})

getSipWidgetConfig.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getSipWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
getSipWidgetConfig.url = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            agentSlug: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        agentSlug: args.agentSlug,
    }

    return getSipWidgetConfig.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{agentSlug}', parsedArgs.agentSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getSipWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
getSipWidgetConfig.get = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: getSipWidgetConfig.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getSipWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
getSipWidgetConfig.head = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: getSipWidgetConfig.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getSipWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
const getSipWidgetConfigForm = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getSipWidgetConfig.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getSipWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
getSipWidgetConfigForm.get = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getSipWidgetConfig.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::getSipWidgetConfig
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
getSipWidgetConfigForm.head = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: getSipWidgetConfig.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

getSipWidgetConfig.form = getSipWidgetConfigForm

const WidgetConfigController = { getWebWidgetConfig, getSipWidgetConfig }

export default WidgetConfigController