<?php

namespace App\Data\Tenant\Agent\Session;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Data;

class CreateAgentSessionTranscriptData extends Data
{
    public function __construct(
        #[Rule('required|string|in:AI,User')]
        public string $speaker,
        #[Rule('required|string')]
        public string $text,
        #[Rule('required|integer|min:0')]
        public int $offset_ms,
    ) {}
}
