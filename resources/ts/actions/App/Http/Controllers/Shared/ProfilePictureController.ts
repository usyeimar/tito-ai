import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
const show5f1ea0325113b1acb52e75270d8f7082 = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show5f1ea0325113b1acb52e75270d8f7082.url(args, options),
    method: 'get',
})

show5f1ea0325113b1acb52e75270d8f7082.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/profile-pictures/{profilePicture}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
show5f1ea0325113b1acb52e75270d8f7082.url = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { profilePicture: args }
    }

    if (Array.isArray(args)) {
        args = {
            profilePicture: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        profilePicture: args.profilePicture,
    }

    return show5f1ea0325113b1acb52e75270d8f7082.definition.url
            .replace('{profilePicture}', parsedArgs.profilePicture.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
show5f1ea0325113b1acb52e75270d8f7082.get = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show5f1ea0325113b1acb52e75270d8f7082.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
show5f1ea0325113b1acb52e75270d8f7082.head = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show5f1ea0325113b1acb52e75270d8f7082.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
const show5f1ea0325113b1acb52e75270d8f7082Form = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show5f1ea0325113b1acb52e75270d8f7082.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
show5f1ea0325113b1acb52e75270d8f7082Form.get = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show5f1ea0325113b1acb52e75270d8f7082.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
show5f1ea0325113b1acb52e75270d8f7082Form.head = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show5f1ea0325113b1acb52e75270d8f7082.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show5f1ea0325113b1acb52e75270d8f7082.form = show5f1ea0325113b1acb52e75270d8f7082Form
/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}'
*/
const show5b299b28c90727c0643be3ef334fd0c6 = (args: { tenant: string | number, profilePicture: string | number } | [tenant: string | number, profilePicture: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show5b299b28c90727c0643be3ef334fd0c6.url(args, options),
    method: 'get',
})

show5b299b28c90727c0643be3ef334fd0c6.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}'
*/
show5b299b28c90727c0643be3ef334fd0c6.url = (args: { tenant: string | number, profilePicture: string | number } | [tenant: string | number, profilePicture: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
            tenant: args[0],
            profilePicture: args[1],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        tenant: args.tenant,
        profilePicture: args.profilePicture,
    }

    return show5b299b28c90727c0643be3ef334fd0c6.definition.url
            .replace('{tenant}', parsedArgs.tenant.toString())
            .replace('{profilePicture}', parsedArgs.profilePicture.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}'
*/
show5b299b28c90727c0643be3ef334fd0c6.get = (args: { tenant: string | number, profilePicture: string | number } | [tenant: string | number, profilePicture: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show5b299b28c90727c0643be3ef334fd0c6.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}'
*/
show5b299b28c90727c0643be3ef334fd0c6.head = (args: { tenant: string | number, profilePicture: string | number } | [tenant: string | number, profilePicture: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show5b299b28c90727c0643be3ef334fd0c6.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}'
*/
const show5b299b28c90727c0643be3ef334fd0c6Form = (args: { tenant: string | number, profilePicture: string | number } | [tenant: string | number, profilePicture: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show5b299b28c90727c0643be3ef334fd0c6.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}'
*/
show5b299b28c90727c0643be3ef334fd0c6Form.get = (args: { tenant: string | number, profilePicture: string | number } | [tenant: string | number, profilePicture: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show5b299b28c90727c0643be3ef334fd0c6.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}'
*/
show5b299b28c90727c0643be3ef334fd0c6Form.head = (args: { tenant: string | number, profilePicture: string | number } | [tenant: string | number, profilePicture: string | number ], options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show5b299b28c90727c0643be3ef334fd0c6.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show5b299b28c90727c0643be3ef334fd0c6.form = show5b299b28c90727c0643be3ef334fd0c6Form

export const show = {
    'https://app.tito.ai/api/profile-pictures/{profilePicture}': show5f1ea0325113b1acb52e75270d8f7082,
    'https://app.tito.ai/{tenant}/api/profile-pictures/{profilePicture}': show5b299b28c90727c0643be3ef334fd0c6,
}

const ProfilePictureController = { show }

export default ProfilePictureController