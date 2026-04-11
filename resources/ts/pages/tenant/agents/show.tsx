import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Sparkles, Save, Trash2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';
import { Switch } from '@/components/ui/switch';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { AgentTestCall } from '@/components/agents/test-call';
import { tenantApi, TenantApiError } from '@/lib/tenant-api';
import { cn } from '@/lib/utils';
import type { Agent, TenantSummary } from '@/types/agent';
import type { BreadcrumbItem } from '@/types';

type Props = {
    tenant: TenantSummary;
    agent: Agent;
};

export default function AgentShow({ tenant, agent: initialAgent }: Props) {
    const [agent, setAgent] = React.useState<Agent>(initialAgent);

    React.useEffect(() => {
        setAgent(initialAgent);
    }, [initialAgent]);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: tenant.name, href: `/${tenant.slug}/dashboard` },
        { title: 'Asistentes', href: `/${tenant.slug}/agents` },
        { title: agent.name, href: `/${tenant.slug}/agents/${agent.id}` },
    ];

    const handleDelete = async () => {
        if (!confirm(`¿Eliminar el asistente "${agent.name}"?`)) return;
        try {
            await tenantApi(tenant.slug, `/ai/agents/${agent.id}`, {
                method: 'DELETE',
            });
            router.visit(`/${tenant.slug}/agents`);
        } catch (err) {
            alert(
                err instanceof TenantApiError
                    ? err.message
                    : 'No se pudo eliminar el asistente',
            );
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={agent.name} />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <header className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div className="flex items-start gap-4">
                        <Link
                            href={`/${tenant.slug}/agents`}
                            className="mt-1 flex size-9 items-center justify-center rounded-lg border border-border text-muted-foreground hover:bg-muted hover:text-foreground"
                        >
                            <ArrowLeft className="size-4" />
                        </Link>
                        <div className="flex items-center gap-3">
                            <div className="flex size-12 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <Sparkles className="size-6" />
                            </div>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight capitalize">
                                    {agent.name}
                                </h1>
                                <p className="text-sm text-muted-foreground">
                                    {agent.slug} · {agent.language}
                                </p>
                            </div>
                        </div>
                    </div>
                    <Button
                        variant="outline"
                        onClick={handleDelete}
                        className="gap-2 text-destructive hover:bg-destructive/10 hover:text-destructive"
                    >
                        <Trash2 className="size-4" />
                        Eliminar
                    </Button>
                </header>

                <Tabs defaultValue="general" className="w-full">
                    <TabsList className="mb-4">
                        <TabsTrigger value="general">General</TabsTrigger>
                        <TabsTrigger value="brain">Cerebro (LLM)</TabsTrigger>
                        <TabsTrigger value="runtime">Voz y Audio</TabsTrigger>
                        <TabsTrigger value="call">Probar llamada</TabsTrigger>
                    </TabsList>
                    
                    <TabsContent value="general">
                        <GeneralTab
                            tenantSlug={tenant.slug}
                            agent={agent}
                            onUpdated={setAgent}
                        />
                    </TabsContent>
                    
                    <TabsContent value="brain">
                        <BrainTab
                            tenantSlug={tenant.slug}
                            agent={agent}
                            onUpdated={setAgent}
                        />
                    </TabsContent>

                    <TabsContent value="runtime">
                        <RuntimeTab
                            tenantSlug={tenant.slug}
                            agent={agent}
                            onUpdated={setAgent}
                        />
                    </TabsContent>
                    
                    <TabsContent value="call">
                        <div className="rounded-2xl border border-border bg-card p-6">
                            <AgentTestCall
                                tenantSlug={tenant.slug}
                                agentId={agent.id}
                                agentName={agent.name}
                            />
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}

function GeneralTab({
    tenantSlug,
    agent,
    onUpdated,
}: {
    tenantSlug: string;
    agent: Agent;
    onUpdated: (a: Agent) => void;
}) {
    const [name, setName] = React.useState(agent.name);
    const [description, setDescription] = React.useState(
        agent.description ?? '',
    );
    const [language, setLanguage] = React.useState(agent.language);
    const [timezone, setTimezone] = React.useState(agent.timezone || 'UTC');
    const [currency, setCurrency] = React.useState(agent.currency || 'COP');
    const [numberFormat, setNumberFormat] = React.useState(agent.number_format || 'dot_decimal');
    const [tags, setTags] = React.useState((agent.tags || []).join(', '));
    const [saving, setSaving] = React.useState(false);
    const [feedback, setFeedback] = React.useState<{
        type: 'ok' | 'error';
        text: string;
    } | null>(null);

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);
        setFeedback(null);
        try {
            const result = await tenantApi<{ data: Agent }>(
                tenantSlug,
                `/ai/agents/${agent.id}`,
                {
                    method: 'PATCH',
                    body: {
                        name,
                        description: description || null,
                        language,
                        timezone,
                        currency,
                        number_format: numberFormat,
                        tags: tags.split(',').map((t) => t.trim()).filter(Boolean),
                    },
                },
            );
            onUpdated(result.data);
            setFeedback({ type: 'ok', text: 'Cambios guardados exitosamente.' });
        } catch (err) {
            setFeedback({
                type: 'error',
                text:
                    err instanceof TenantApiError
                        ? err.message
                        : 'No se pudieron guardar los cambios',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <form
            onSubmit={handleSave}
            className="grid max-w-3xl gap-6 rounded-2xl border border-border bg-card p-6"
        >
            <div className="grid gap-2">
                <Label htmlFor="name">Nombre</Label>
                <Input
                    id="name"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="description">Descripción</Label>
                <Textarea
                    id="description"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    rows={3}
                    placeholder="Ej. Asistente comercial encargado de captar leads..."
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="tags">Etiquetas (separadas por comas)</Label>
                <Input
                    id="tags"
                    value={tags}
                    onChange={(e) => setTags(e.target.value)}
                    placeholder="Ej. ventas, soporte, web-only"
                />
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="language">Idioma (Locale)</Label>
                    <Select value={language} onValueChange={setLanguage}>
                        <SelectTrigger id="language" className="w-full">
                            <SelectValue placeholder="Selecciona un idioma" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="es">Español (Genérico)</SelectItem>
                            <SelectItem value="es-CO">Español (Colombia)</SelectItem>
                            <SelectItem value="es-ES">Español (España)</SelectItem>
                            <SelectItem value="es-MX">Español (México)</SelectItem>
                            <SelectItem value="en-US">English (US)</SelectItem>
                            <SelectItem value="pt-BR">Portugués (Brasil)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="timezone">Zona horaria</Label>
                    <Input
                        id="timezone"
                        value={timezone}
                        onChange={(e) => setTimezone(e.target.value)}
                        placeholder="UTC o America/Bogota"
                    />
                </div>
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="currency">Moneda</Label>
                    <Input
                        id="currency"
                        value={currency}
                        onChange={(e) => setCurrency(e.target.value)}
                        placeholder="Ej. COP, USD, EUR"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="numberFormat">Formato Numérico</Label>
                    <Select value={numberFormat} onValueChange={setNumberFormat}>
                        <SelectTrigger id="numberFormat" className="w-full">
                            <SelectValue placeholder="Selecciona un formato" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="dot_decimal">Punto decimal (1,000.50)</SelectItem>
                            <SelectItem value="comma_decimal">Coma decimal (1.000,50)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="flex items-center gap-3 pt-4">
                <Button type="submit" disabled={saving} className="gap-2">
                    <Save className="size-4" />
                    {saving ? 'Guardando...' : 'Guardar cambios'}
                </Button>
                {feedback && (
                    <span
                        className={cn(
                            'text-sm',
                            feedback.type === 'ok'
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-destructive',
                        )}
                    >
                        {feedback.text}
                    </span>
                )}
            </div>
        </form>
    );
}

function BrainTab({
    tenantSlug,
    agent,
    onUpdated,
}: {
    tenantSlug: string;
    agent: Agent;
    onUpdated: (a: Agent) => void;
}) {
    const brain = agent.brain_config ?? {};
    const [provider, setProvider] = React.useState(
        (brain.provider as string) ?? 'openai',
    );
    const [model, setModel] = React.useState(
        (brain.model as string) ?? 'gpt-4o-mini',
    );
    const [temperature, setTemperature] = React.useState(
        typeof brain.temperature === 'number' ? brain.temperature : 0.7,
    );
    const [maxTokens, setMaxTokens] = React.useState(
        typeof brain.max_tokens === 'number' ? brain.max_tokens : 1024,
    );
    const [systemPrompt, setSystemPrompt] = React.useState(
        (brain.system_prompt as string) ?? '',
    );
    const [saving, setSaving] = React.useState(false);
    const [feedback, setFeedback] = React.useState<{
        type: 'ok' | 'error';
        text: string;
    } | null>(null);

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);
        setFeedback(null);
        try {
            const result = await tenantApi<{ data: Agent }>(
                tenantSlug,
                `/ai/agents/${agent.id}`,
                {
                    method: 'PATCH',
                    body: {
                        brain_config: {
                            ...brain,
                            provider,
                            model,
                            temperature,
                            max_tokens: maxTokens,
                            system_prompt: systemPrompt,
                        },
                    },
                },
            );
            onUpdated(result.data);
            setFeedback({ type: 'ok', text: 'Cerebro actualizado exitosamente.' });
        } catch (err) {
            setFeedback({
                type: 'error',
                text:
                    err instanceof TenantApiError
                        ? err.message
                        : 'No se pudieron guardar los cambios',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <form
            onSubmit={handleSave}
            className="grid max-w-3xl gap-6 rounded-2xl border border-border bg-card p-6"
        >
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="provider">Proveedor LLM</Label>
                    <Select value={provider} onValueChange={setProvider}>
                        <SelectTrigger id="provider" className="w-full">
                            <SelectValue placeholder="Selecciona un proveedor" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="openai">OpenAI</SelectItem>
                            <SelectItem value="anthropic">Anthropic</SelectItem>
                            <SelectItem value="google">Google</SelectItem>
                            <SelectItem value="groq">Groq</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="model">Modelo</Label>
                    <Input
                        id="model"
                        value={model}
                        onChange={(e) => setModel(e.target.value)}
                        placeholder="gpt-4o-mini"
                    />
                </div>
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-2">
                <div className="grid gap-4">
                    <div className="flex justify-between">
                        <Label htmlFor="temperature">Temperatura</Label>
                        <span className="text-sm font-medium text-muted-foreground">{temperature.toFixed(2)}</span>
                    </div>
                    <Slider
                        id="temperature"
                        min={0}
                        max={2}
                        step={0.05}
                        value={[temperature]}
                        onValueChange={(vals) => setTemperature(vals[0])}
                        className="w-full"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="maxTokens">Max Tokens</Label>
                    <Input
                        id="maxTokens"
                        type="number"
                        value={maxTokens}
                        onChange={(e) => setMaxTokens(parseInt(e.target.value) || 1024)}
                        placeholder="Ej. 1024"
                    />
                </div>
            </div>

            <div className="grid gap-2 mt-2">
                <Label htmlFor="system">Prompt del Sistema (Instructions)</Label>
                <Textarea
                    id="system"
                    value={systemPrompt}
                    onChange={(e) => setSystemPrompt(e.target.value)}
                    rows={8}
                    placeholder="Eres un asistente útil y amable que..."
                    className="font-mono text-sm"
                />
            </div>

            <div className="flex items-center gap-3 pt-4">
                <Button type="submit" disabled={saving} className="gap-2">
                    <Save className="size-4" />
                    {saving ? 'Guardando...' : 'Guardar cerebro'}
                </Button>
                {feedback && (
                    <span
                        className={cn(
                            'text-sm',
                            feedback.type === 'ok'
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-destructive',
                        )}
                    >
                        {feedback.text}
                    </span>
                )}
            </div>
        </form>
    );
}

function RuntimeTab({
    tenantSlug,
    agent,
    onUpdated,
}: {
    tenantSlug: string;
    agent: Agent;
    onUpdated: (a: Agent) => void;
}) {
    const runtime = agent.runtime_config ?? {};
    
    // STT Defaults
    const sttConfig = runtime.stt ?? {};
    const [sttProvider, setSttProvider] = React.useState((sttConfig.provider as string) ?? 'deepgram');
    const [sttModel, setSttModel] = React.useState((sttConfig.model as string) ?? 'nova-2');
    
    // TTS Defaults
    const ttsConfig = runtime.tts ?? {};
    const [ttsProvider, setTtsProvider] = React.useState((ttsConfig.provider as string) ?? 'cartesia');
    const [ttsVoiceId, setTtsVoiceId] = React.useState((ttsConfig.voice_id as string) ?? '79a125e8-cd45-4c13-8a67-188112f4dd22');

    // Behavior Defaults
    const behavior = runtime.behavior ?? {};
    const [interruptibility, setInterruptibility] = React.useState<boolean>(behavior.interruptibility ?? true);
    const [initialAction, setInitialAction] = React.useState<string>(behavior.initial_action ?? 'SPEAK_FIRST');
    const [streaming, setStreaming] = React.useState<boolean>(behavior.streaming ?? true);

    // Session Limits Defaults
    const sessionLimits = runtime.session_limits ?? {};
    const inactivity = sessionLimits.inactivity_timeout ?? {};
    const [maxDuration, setMaxDuration] = React.useState<number>(sessionLimits.max_duration_seconds ?? 600);
    const [inactivityEnabled, setInactivityEnabled] = React.useState<boolean>(inactivity.enabled ?? true);
    const [inactivityWait, setInactivityWait] = React.useState<number>(inactivity.steps?.[0]?.wait_seconds ?? 15);
    const [inactivityMsg, setInactivityMsg] = React.useState<string>(inactivity.steps?.[0]?.message?.[0] ?? '¿Sigues ahí?');
    const [inactivityFinal, setInactivityFinal] = React.useState<string>(inactivity.final_message ?? 'Cierro la sesión por inactividad. ¡Escríbenos cuando quieras!');

    const [saving, setSaving] = React.useState(false);
    const [feedback, setFeedback] = React.useState<{
        type: 'ok' | 'error';
        text: string;
    } | null>(null);

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setSaving(true);
        setFeedback(null);
        try {
            const result = await tenantApi<{ data: Agent }>(
                tenantSlug,
                `/ai/agents/${agent.id}`,
                {
                    method: 'PATCH',
                    body: {
                        runtime_config: {
                            ...runtime,
                            stt: { provider: sttProvider, model: sttModel },
                            tts: { provider: ttsProvider, voice_id: ttsVoiceId },
                            behavior: {
                                interruptibility,
                                initial_action: initialAction,
                                streaming,
                            },
                            session_limits: {
                                max_duration_seconds: maxDuration,
                                inactivity_timeout: {
                                    enabled: inactivityEnabled,
                                    steps: [
                                        {
                                            wait_seconds: inactivityWait,
                                            message: [inactivityMsg]
                                        }
                                    ],
                                    final_message: inactivityFinal
                                }
                            }
                        },
                    },
                },
            );
            onUpdated(result.data);
            setFeedback({ type: 'ok', text: 'Configuración de runtime actualizada.' });
        } catch (err) {
            setFeedback({
                type: 'error',
                text:
                    err instanceof TenantApiError
                        ? err.message
                        : 'No se pudieron guardar los cambios',
            });
        } finally {
            setSaving(false);
        }
    };

    return (
        <form
            onSubmit={handleSave}
            className="grid max-w-3xl gap-6 rounded-2xl border border-border bg-card p-6"
        >
            <div className="grid gap-2">
                <h3 className="text-lg font-semibold tracking-tight">Voz a Texto (STT)</h3>
                <p className="text-sm text-muted-foreground">Configura el proveedor encargado de transcribir la voz del usuario.</p>
            </div>
            
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="stt-provider">Proveedor STT</Label>
                    <Select value={sttProvider} onValueChange={setSttProvider}>
                        <SelectTrigger id="stt-provider" className="w-full">
                            <SelectValue placeholder="Selecciona un proveedor" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="deepgram">Deepgram</SelectItem>
                            <SelectItem value="gladia">Gladia</SelectItem>
                            <SelectItem value="openai">OpenAI (Whisper)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="stt-model">Modelo de Transcripción</Label>
                    <Input
                        id="stt-model"
                        value={sttModel}
                        onChange={(e) => setSttModel(e.target.value)}
                        placeholder="nova-2"
                    />
                </div>
            </div>

            <div className="my-2 border-t border-border" />

            <div className="grid gap-2">
                <h3 className="text-lg font-semibold tracking-tight">Texto a Voz (TTS)</h3>
                <p className="text-sm text-muted-foreground">Configura el proveedor y la voz que el asistente utilizará para hablar.</p>
            </div>
            
            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="tts-provider">Proveedor TTS</Label>
                    <Select value={ttsProvider} onValueChange={setTtsProvider}>
                        <SelectTrigger id="tts-provider" className="w-full">
                            <SelectValue placeholder="Selecciona un proveedor" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="cartesia">Cartesia</SelectItem>
                            <SelectItem value="elevenlabs">ElevenLabs</SelectItem>
                            <SelectItem value="openai">OpenAI (TTS)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="tts-voice">ID de Voz (Voice ID)</Label>
                    <Input
                        id="tts-voice"
                        value={ttsVoiceId}
                        onChange={(e) => setTtsVoiceId(e.target.value)}
                        placeholder="Ej. 79a125e8-cd45..."
                    />
                </div>
            </div>

            <div className="my-2 border-t border-border" />

            <div className="grid gap-2">
                <h3 className="text-lg font-semibold tracking-tight">Comportamiento (Behavior)</h3>
                <p className="text-sm text-muted-foreground">Reglas de interacción durante la llamada.</p>
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="initial-action">Acción Inicial</Label>
                    <Select value={initialAction} onValueChange={setInitialAction}>
                        <SelectTrigger id="initial-action" className="w-full">
                            <SelectValue placeholder="Selecciona acción" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="SPEAK_FIRST">Hablar primero (Speak First)</SelectItem>
                            <SelectItem value="LISTEN_FIRST">Escuchar primero (Listen First)</SelectItem>
                            <SelectItem value="WAIT">Esperar (Wait)</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div className="grid gap-4 mt-2">
                    <div className="flex items-center justify-between">
                        <Label htmlFor="interruptibility" className="flex flex-col gap-1">
                            <span>Interrumpible</span>
                            <span className="font-normal text-xs text-muted-foreground">Permite al usuario interrumpir al asistente.</span>
                        </Label>
                        <Switch id="interruptibility" checked={interruptibility} onCheckedChange={setInterruptibility} />
                    </div>
                    <div className="flex items-center justify-between">
                        <Label htmlFor="streaming" className="flex flex-col gap-1">
                            <span>Streaming</span>
                            <span className="font-normal text-xs text-muted-foreground">Procesa audio en tiempo real.</span>
                        </Label>
                        <Switch id="streaming" checked={streaming} onCheckedChange={setStreaming} />
                    </div>
                </div>
            </div>

            <div className="my-2 border-t border-border" />

            <div className="grid gap-2">
                <h3 className="text-lg font-semibold tracking-tight">Límites de Sesión</h3>
                <p className="text-sm text-muted-foreground">Controla tiempos máximos de llamada y timeouts de inactividad.</p>
            </div>

            <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="max-duration">Duración máxima (segundos)</Label>
                    <Input
                        id="max-duration"
                        type="number"
                        value={maxDuration}
                        onChange={(e) => setMaxDuration(parseInt(e.target.value) || 600)}
                        placeholder="Ej. 600"
                    />
                </div>
                <div className="flex flex-col gap-4">
                    <div className="flex items-center justify-between mt-2">
                        <Label htmlFor="inactivity-enabled" className="flex flex-col gap-1">
                            <span>Timeout por inactividad</span>
                            <span className="font-normal text-xs text-muted-foreground">Finaliza la llamada si el usuario no habla.</span>
                        </Label>
                        <Switch id="inactivity-enabled" checked={inactivityEnabled} onCheckedChange={setInactivityEnabled} />
                    </div>
                </div>
            </div>

            {inactivityEnabled && (
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 mt-4 rounded-xl border border-border bg-muted/20 p-4">
                    <div className="grid gap-2">
                        <Label htmlFor="inactivity-wait">Segundos de espera</Label>
                        <Input
                            id="inactivity-wait"
                            type="number"
                            value={inactivityWait}
                            onChange={(e) => setInactivityWait(parseInt(e.target.value) || 15)}
                            placeholder="Ej. 15"
                        />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="inactivity-msg">Mensaje de aviso</Label>
                        <Input
                            id="inactivity-msg"
                            value={inactivityMsg}
                            onChange={(e) => setInactivityMsg(e.target.value)}
                            placeholder="¿Sigues ahí?"
                        />
                    </div>
                    <div className="grid gap-2 sm:col-span-2">
                        <Label htmlFor="inactivity-final">Mensaje de despedida</Label>
                        <Input
                            id="inactivity-final"
                            value={inactivityFinal}
                            onChange={(e) => setInactivityFinal(e.target.value)}
                            placeholder="Cierro la sesión por inactividad."
                        />
                    </div>
                </div>
            )}

            <div className="flex items-center gap-3 pt-4">
                <Button type="submit" disabled={saving} className="gap-2">
                    <Save className="size-4" />
                    {saving ? 'Guardando...' : 'Guardar configuración'}
                </Button>
                {feedback && (
                    <span
                        className={cn(
                            'text-sm',
                            feedback.type === 'ok'
                                ? 'text-emerald-600 dark:text-emerald-400'
                                : 'text-destructive',
                        )}
                    >
                        {feedback.text}
                    </span>
                )}
            </div>
        </form>
    );
}
