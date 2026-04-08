import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::verifyTfa
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:22
* @route 'https://app.tito.ai/api/auth/tfa/verify'
*/
export const verifyTfa = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyTfa.url(options),
    method: 'post',
})

verifyTfa.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/tfa/verify',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::verifyTfa
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:22
* @route 'https://app.tito.ai/api/auth/tfa/verify'
*/
verifyTfa.url = (options?: RouteQueryOptions) => {
    return verifyTfa.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::verifyTfa
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:22
* @route 'https://app.tito.ai/api/auth/tfa/verify'
*/
verifyTfa.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: verifyTfa.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::verifyTfa
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:22
* @route 'https://app.tito.ai/api/auth/tfa/verify'
*/
const verifyTfaForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: verifyTfa.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::verifyTfa
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:22
* @route 'https://app.tito.ai/api/auth/tfa/verify'
*/
verifyTfaForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: verifyTfa.url(options),
    method: 'post',
})

verifyTfa.form = verifyTfaForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaChallenge
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:29
* @route 'https://app.tito.ai/api/auth/tfa/challenge'
*/
export const tfaChallenge = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaChallenge.url(options),
    method: 'post',
})

tfaChallenge.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/tfa/challenge',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaChallenge
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:29
* @route 'https://app.tito.ai/api/auth/tfa/challenge'
*/
tfaChallenge.url = (options?: RouteQueryOptions) => {
    return tfaChallenge.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaChallenge
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:29
* @route 'https://app.tito.ai/api/auth/tfa/challenge'
*/
tfaChallenge.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaChallenge.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaChallenge
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:29
* @route 'https://app.tito.ai/api/auth/tfa/challenge'
*/
const tfaChallengeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaChallenge.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaChallenge
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:29
* @route 'https://app.tito.ai/api/auth/tfa/challenge'
*/
tfaChallengeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaChallenge.url(options),
    method: 'post',
})

tfaChallenge.form = tfaChallengeForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaEnable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:39
* @route 'https://app.tito.ai/api/auth/tfa/enable'
*/
export const tfaEnable = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaEnable.url(options),
    method: 'post',
})

tfaEnable.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/tfa/enable',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaEnable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:39
* @route 'https://app.tito.ai/api/auth/tfa/enable'
*/
tfaEnable.url = (options?: RouteQueryOptions) => {
    return tfaEnable.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaEnable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:39
* @route 'https://app.tito.ai/api/auth/tfa/enable'
*/
tfaEnable.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaEnable.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaEnable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:39
* @route 'https://app.tito.ai/api/auth/tfa/enable'
*/
const tfaEnableForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaEnable.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaEnable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:39
* @route 'https://app.tito.ai/api/auth/tfa/enable'
*/
tfaEnableForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaEnable.url(options),
    method: 'post',
})

tfaEnable.form = tfaEnableForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaConfirm
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:48
* @route 'https://app.tito.ai/api/auth/tfa/confirm'
*/
export const tfaConfirm = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaConfirm.url(options),
    method: 'post',
})

tfaConfirm.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/tfa/confirm',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaConfirm
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:48
* @route 'https://app.tito.ai/api/auth/tfa/confirm'
*/
tfaConfirm.url = (options?: RouteQueryOptions) => {
    return tfaConfirm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaConfirm
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:48
* @route 'https://app.tito.ai/api/auth/tfa/confirm'
*/
tfaConfirm.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaConfirm.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaConfirm
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:48
* @route 'https://app.tito.ai/api/auth/tfa/confirm'
*/
const tfaConfirmForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaConfirm.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaConfirm
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:48
* @route 'https://app.tito.ai/api/auth/tfa/confirm'
*/
tfaConfirmForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaConfirm.url(options),
    method: 'post',
})

tfaConfirm.form = tfaConfirmForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:58
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes'
*/
export const tfaRecoveryCodes = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tfaRecoveryCodes.url(options),
    method: 'get',
})

tfaRecoveryCodes.definition = {
    methods: ["get","head"],
    url: 'https://app.tito.ai/api/auth/tfa/recovery-codes',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:58
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes'
*/
tfaRecoveryCodes.url = (options?: RouteQueryOptions) => {
    return tfaRecoveryCodes.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:58
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes'
*/
tfaRecoveryCodes.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: tfaRecoveryCodes.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:58
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes'
*/
tfaRecoveryCodes.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: tfaRecoveryCodes.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:58
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes'
*/
const tfaRecoveryCodesForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: tfaRecoveryCodes.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:58
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes'
*/
tfaRecoveryCodesForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: tfaRecoveryCodes.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:58
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes'
*/
tfaRecoveryCodesForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: tfaRecoveryCodes.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

tfaRecoveryCodes.form = tfaRecoveryCodesForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRegenerateRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:65
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes/regenerate'
*/
export const tfaRegenerateRecoveryCodes = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaRegenerateRecoveryCodes.url(options),
    method: 'post',
})

tfaRegenerateRecoveryCodes.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/tfa/recovery-codes/regenerate',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRegenerateRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:65
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes/regenerate'
*/
tfaRegenerateRecoveryCodes.url = (options?: RouteQueryOptions) => {
    return tfaRegenerateRecoveryCodes.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRegenerateRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:65
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes/regenerate'
*/
tfaRegenerateRecoveryCodes.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaRegenerateRecoveryCodes.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRegenerateRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:65
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes/regenerate'
*/
const tfaRegenerateRecoveryCodesForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaRegenerateRecoveryCodes.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaRegenerateRecoveryCodes
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:65
* @route 'https://app.tito.ai/api/auth/tfa/recovery-codes/regenerate'
*/
tfaRegenerateRecoveryCodesForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaRegenerateRecoveryCodes.url(options),
    method: 'post',
})

tfaRegenerateRecoveryCodes.form = tfaRegenerateRecoveryCodesForm

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaDisable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:72
* @route 'https://app.tito.ai/api/auth/tfa/disable'
*/
export const tfaDisable = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaDisable.url(options),
    method: 'post',
})

tfaDisable.definition = {
    methods: ["post"],
    url: 'https://app.tito.ai/api/auth/tfa/disable',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaDisable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:72
* @route 'https://app.tito.ai/api/auth/tfa/disable'
*/
tfaDisable.url = (options?: RouteQueryOptions) => {
    return tfaDisable.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaDisable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:72
* @route 'https://app.tito.ai/api/auth/tfa/disable'
*/
tfaDisable.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: tfaDisable.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaDisable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:72
* @route 'https://app.tito.ai/api/auth/tfa/disable'
*/
const tfaDisableForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaDisable.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Central\API\Auth\Tfa\TfaController::tfaDisable
* @see app/Http/Controllers/Central/API/Auth/Tfa/TfaController.php:72
* @route 'https://app.tito.ai/api/auth/tfa/disable'
*/
tfaDisableForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: tfaDisable.url(options),
    method: 'post',
})

tfaDisable.form = tfaDisableForm

const TfaController = { verifyTfa, tfaChallenge, tfaEnable, tfaConfirm, tfaRecoveryCodes, tfaRegenerateRecoveryCodes, tfaDisable }

export default TfaController