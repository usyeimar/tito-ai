<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\API\Agent;

use Illuminate\Foundation\Http\FormRequest;

class RunnerWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Auth handled by VerifyRunnerSignature middleware
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'event' => ['required', 'string', 'in:session.started,session.ended,session.transcript,session.error'],
            'agent_id' => ['required', 'string'],
            'session_id' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
            'data.session_id' => ['nullable', 'string'],
            'data.channel' => ['nullable', 'string'],
            'data.status' => ['nullable', 'string'],
            'data.duration' => ['nullable', 'numeric'],
            'data.duration_seconds' => ['nullable', 'numeric'],
            'data.reason' => ['nullable', 'string'],
            'data.recording_path' => ['nullable', 'string'],
            'data.transcription' => ['nullable', 'array'],
            'data.transcription.*.role' => ['nullable', 'string'],
            'data.transcription.*.content' => ['nullable', 'string'],
            'data.role' => ['nullable', 'string'],
            'data.content' => ['nullable', 'string'],
            'data.text' => ['nullable', 'string'],
            'data.error' => ['nullable', 'string'],
        ];
    }
}
