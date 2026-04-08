import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
export const show = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/profile-pictures/{profilePicture}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
show.url = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions) => {
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

    return show.definition.url
            .replace('{profilePicture}', parsedArgs.profilePicture.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
show.get = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
show.head = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
const showForm = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
showForm.get = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Shared\ProfilePictureController::show
* @see app/Http/Controllers/Shared/ProfilePictureController.php:15
* @route 'https://app.tito.ai/api/profile-pictures/{profilePicture}'
*/
showForm.head = (args: { profilePicture: string | number } | [profilePicture: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

const profilePicture = {
    show: Object.assign(show, show),
}

export default profilePicture