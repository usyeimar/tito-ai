<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Agent;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTrunkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy handles authorization
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'agent_id' => ['nullable', 'string', 'ulid'],
            'workspace_slug' => ['sometimes', 'string', 'max:100'],
            'mode' => ['sometimes', 'string', 'in:inbound,register,outbound'],
            'max_concurrent_calls' => ['sometimes', 'integer', 'min:1', 'max:1000'],
            'codecs' => ['sometimes', 'array'],
            'codecs.*' => ['string', 'in:ulaw,alaw,g722,opus'],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'inbound_auth' => ['nullable', 'array'],
            'inbound_auth.auth_type' => ['nullable', 'string', 'in:ip,userpass'],
            'inbound_auth.allowed_ips' => ['nullable', 'array'],
            'inbound_auth.allowed_ips.*' => ['string'],
            'inbound_auth.username' => ['nullable', 'string'],
            'inbound_auth.password' => ['nullable', 'string'],
            'routes' => ['nullable', 'array'],
            'routes.*.pattern' => ['required_with:routes', 'string'],
            'routes.*.agent_id' => ['nullable', 'string', 'ulid'],
            'routes.*.priority' => ['nullable', 'integer', 'min:0'],
            'routes.*.enabled' => ['nullable', 'boolean'],
            'sip_host' => ['nullable', 'string', 'max:255'],
            'sip_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'register_config' => ['nullable', 'array'],
            'register_config.server' => ['nullable', 'string'],
            'register_config.port' => ['nullable', 'integer'],
            'register_config.username' => ['nullable', 'string'],
            'register_config.password' => ['nullable', 'string'],
            'register_config.register_interval' => ['nullable', 'integer'],
            'outbound' => ['nullable', 'array'],
            'outbound.trunk_name' => ['nullable', 'string'],
            'outbound.server' => ['nullable', 'string'],
            'outbound.port' => ['nullable', 'integer'],
            'outbound.username' => ['nullable', 'string'],
            'outbound.password' => ['nullable', 'string'],
            'outbound.caller_id' => ['nullable', 'string'],
        ];
    }
}
