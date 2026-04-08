<?php

namespace App\Actions\Tenant\Agent\Session;

use App\Data\Tenant\Agent\Session\CreateAgentSessionTranscriptData;
use App\Models\Tenant\Agent\AgentSession;
use App\Models\Tenant\Agent\AgentSessionTranscript;

class CreateSessionTranscript
{
    public function __invoke(AgentSession $session, CreateAgentSessionTranscriptData $data): AgentSessionTranscript
    {
        return $session->transcripts()->create($data->toArray());
    }
}
