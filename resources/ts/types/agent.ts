export type AgentBrainConfig = {
    provider?: string;
    model?: string;
    temperature?: number;
    system_prompt?: string;
    [key: string]: unknown;
};

export type AgentRuntimeConfig = {
    voice?: string;
    stt_provider?: string;
    tts_provider?: string;
    [key: string]: unknown;
};

export type Agent = {
    id: string;
    name: string;
    slug: string;
    description: string | null;
    language: string;
    tags: string[] | null;
    timezone: string;
    currency: string;
    number_format: string;
    knowledge_base_id: string | null;
    brain_config: AgentBrainConfig | null;
    runtime_config: AgentRuntimeConfig | null;
    architecture_config: Record<string, unknown> | null;
    capabilities_config: Record<string, unknown> | null;
    observability_config: Record<string, unknown> | null;
    created_at: string | null;
    updated_at: string | null;
};

export type TenantSummary = {
    id: string;
    name: string;
    slug: string;
};
