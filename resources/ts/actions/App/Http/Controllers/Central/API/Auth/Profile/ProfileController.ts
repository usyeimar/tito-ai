import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::update
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:19
* @route 'https://app.tito.ai/api/auth/me'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/api/auth/me',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::update
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:19
* @route 'https://app.tito.ai/api/auth/me'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::update
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:19
* @route 'https://app.tito.ai/api/auth/me'
*/
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::update
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:19
* @route 'https://app.tito.ai/api/auth/me'
*/
const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::update
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:19
* @route 'https://app.tito.ai/api/auth/me'
*/
updateForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updateProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:33
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
export const updateProfilePicture = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateProfilePicture.url(options),
    method: 'post',
})

updateProfilePicture.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/me/profile-picture',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updateProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:33
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
updateProfilePicture.url = (options?: RouteQueryOptions) => {
    return updateProfilePicture.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updateProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:33
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
updateProfilePicture.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: updateProfilePicture.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updateProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:33
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
const updateProfilePictureForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateProfilePicture.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updateProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:33
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
updateProfilePictureForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updateProfilePicture.url(options),
    method: 'post',
})

updateProfilePicture.form = updateProfilePictureForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::removeProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:43
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
export const removeProfilePicture = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeProfilePicture.url(options),
    method: 'delete',
})

removeProfilePicture.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/api/auth/me/profile-picture',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::removeProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:43
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
removeProfilePicture.url = (options?: RouteQueryOptions) => {
    return removeProfilePicture.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::removeProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:43
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
removeProfilePicture.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: removeProfilePicture.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::removeProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:43
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
const removeProfilePictureForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: removeProfilePicture.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::removeProfilePicture
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:43
* @route 'https://app.tito.ai/api/auth/me/profile-picture'
*/
removeProfilePictureForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: removeProfilePicture.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

removeProfilePicture.form = removeProfilePictureForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updatePassword
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:26
* @route 'https://app.tito.ai/api/auth/me/password'
*/
export const updatePassword = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePassword.url(options),
    method: 'patch',
})

updatePassword.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/api/auth/me/password',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updatePassword
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:26
* @route 'https://app.tito.ai/api/auth/me/password'
*/
updatePassword.url = (options?: RouteQueryOptions) => {
    return updatePassword.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updatePassword
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:26
* @route 'https://app.tito.ai/api/auth/me/password'
*/
updatePassword.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: updatePassword.url(options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updatePassword
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:26
* @route 'https://app.tito.ai/api/auth/me/password'
*/
const updatePasswordForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePassword.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Profile\ProfileController::updatePassword
* @see app/Http/Controllers/Central/API/Auth/Profile/ProfileController.php:26
* @route 'https://app.tito.ai/api/auth/me/password'
*/
updatePasswordForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: updatePassword.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

updatePassword.form = updatePasswordForm

const ProfileController = { update, updateProfilePicture, removeProfilePicture, updatePassword }

export default ProfileController