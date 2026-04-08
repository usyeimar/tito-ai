import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::web
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
export const web = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: web.url(args, options),
    method: 'get',
})

web.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::web
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
web.url = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions) => {
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

    return web.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{agentSlug}', parsedArgs.agentSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::web
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
web.get = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: web.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::web
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
web.head = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: web.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::web
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
const webForm = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: web.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::web
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
webForm.get = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: web.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::web
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:16
* @route 'https://app.tito.ai/{tenant}/api/widget-config/web/{agentSlug}'
*/
webForm.head = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: web.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

web.form = webForm

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::sip
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
export const sip = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sip.url(args, options),
    method: 'get',
})

sip.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::sip
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
sip.url = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions) => {
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

    return sip.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{agentSlug}', parsedArgs.agentSlug.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::sip
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
sip.get = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: sip.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::sip
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
sip.head = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: sip.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::sip
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
const sipForm = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: sip.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::sip
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
sipForm.get = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: sip.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\API\Public\Widget\WidgetConfigController::sip
* @see app/Http/Controllers/Tenant/API/Public/Widget/WidgetConfigController.php:74
* @route 'https://app.tito.ai/{tenant}/api/widget-config/sip/{agentSlug}'
*/
sipForm.head = (args: { tenant: string | number, agentSlug: string | number } | [tenant: string | number, agentSlug: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: sip.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

sip.form = sipForm

const widgetConfig = {
    web: Object.assign(web, web),
    sip: Object.assign(sip, sip),
}

export default widgetConfig