import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::edit
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:20
* @route 'https://app.tito.ai/settings/profile'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/settings/profile',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::edit
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:20
* @route 'https://app.tito.ai/settings/profile'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::edit
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:20
* @route 'https://app.tito.ai/settings/profile'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::edit
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:20
* @route 'https://app.tito.ai/settings/profile'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::edit
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:20
* @route 'https://app.tito.ai/settings/profile'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::edit
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:20
* @route 'https://app.tito.ai/settings/profile'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::edit
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:20
* @route 'https://app.tito.ai/settings/profile'
*/
editForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::update
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:31
* @route 'https://app.tito.ai/settings/profile'
*/
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: 'https://app.tito.ai/settings/profile',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::update
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:31
* @route 'https://app.tito.ai/settings/profile'
*/
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::update
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:31
* @route 'https://app.tito.ai/settings/profile'
*/
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::update
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:31
* @route 'https://app.tito.ai/settings/profile'
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
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::update
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:31
* @route 'https://app.tito.ai/settings/profile'
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
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::destroy
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:47
* @route 'https://app.tito.ai/settings/profile'
*/
export const destroy = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: 'https://app.tito.ai/settings/profile',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::destroy
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:47
* @route 'https://app.tito.ai/settings/profile'
*/
destroy.url = (options?: RouteQueryOptions) => {
    return destroy.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::destroy
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:47
* @route 'https://app.tito.ai/settings/profile'
*/
destroy.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::destroy
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:47
* @route 'https://app.tito.ai/settings/profile'
*/
const destroyForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Tenant\Web\Settings\ProfileController::destroy
* @see app/Http/Controllers/Tenant/Web/Settings/ProfileController.php:47
* @route 'https://app.tito.ai/settings/profile'
*/
destroyForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const ProfileController = { edit, update, destroy }

export default ProfileController