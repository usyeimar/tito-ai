import React from 'react';
import { Mic, MicOff, Phone, PhoneOff } from 'lucide-react';
import {
    Room,
    RoomEvent,
    Track,
    type LocalAudioTrack,
    type LocalTrackPublication,
    type RemoteTrack,
    type RemoteTrackPublication,
    type RemoteParticipant,
} from 'livekit-client';
import { Button } from '@/components/ui/button';
import { tenantApi, TenantApiError } from '@/lib/tenant-api';
import { cn } from '@/lib/utils';

type CallStatus =
    | 'idle'
    | 'connecting'
    | 'connected'
    | 'ending'
    | 'error'
    | 'post-call';

type SessionResponse = {
    data: {
        session_id: string;
        room_name: string;
        provider: string;
        url: string;
        access_token: string;
        channel_id: string;
    };
};

type SessionStatus = {
    status: 'active' | 'ended';
    ended_by?: 'agent' | 'user';
    ended_at?: string;
    data?: Record<string, unknown>;
};

type Props = {
    tenantSlug: string;
    agentId: string;
    agentName: string;
};

const POLL_INTERVAL = 1000; // 1 second

export function AgentTestCall({ tenantSlug, agentId, agentName }: Props) {
    const [status, setStatus] = React.useState<CallStatus>('idle');
    const [muted, setMuted] = React.useState(false);
    const [errorMsg, setErrorMsg] = React.useState<string | null>(null);
    const [elapsed, setElapsed] = React.useState(0);
    const [provider, setProvider] = React.useState<string | null>(null);
    const [postCallData, setPostCallData] = React.useState<Record<
        string,
        unknown
    > | null>(null);

    const roomRef = React.useRef<Room | null>(null);
    const sessionIdRef = React.useRef<string | null>(null);
    const channelIdRef = React.useRef<string | null>(null);
    const audioElRef = React.useRef<HTMLAudioElement | null>(null);
    const timerRef = React.useRef<number | null>(null);
    const pollRef = React.useRef<number | null>(null);

    React.useEffect(() => {
        return () => {
            stopTimer();
            stopPolling();
            void roomRef.current?.disconnect();
            roomRef.current = null;
            void terminateRunnerSession();
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const startTimer = () => {
        const startedAt = Date.now();
        timerRef.current = window.setInterval(() => {
            setElapsed(Math.floor((Date.now() - startedAt) / 1000));
        }, 500);
    };

    const stopTimer = () => {
        if (timerRef.current !== null) {
            window.clearInterval(timerRef.current);
            timerRef.current = null;
        }
        setElapsed(0);
    };

    const startPolling = React.useCallback(() => {
        stopPolling();
        pollRef.current = window.setInterval(async () => {
            const channelId = channelIdRef.current;
            if (!channelId) return;

            try {
                const response = await tenantApi<{ data: SessionStatus }>(
                    tenantSlug,
                    `/api/ai/runner/sessions/${channelId}/status`,
                    { method: 'GET' },
                );

                const sessionData = response.data;

                // Check if session ended
                if (
                    sessionData.status === 'ended' &&
                    sessionData.ended_by === 'agent'
                ) {
                    stopPolling();
                    stopTimer();

                    // Disconnect from LiveKit room
                    await roomRef.current?.disconnect();
                    roomRef.current = null;

                    // Show post-call state
                    setPostCallData(sessionData.data || {});
                    setStatus('post-call');
                }
            } catch {
                // Silently ignore polling errors
            }
        }, POLL_INTERVAL);
    }, [tenantSlug]);

    const stopPolling = () => {
        if (pollRef.current !== null) {
            window.clearInterval(pollRef.current);
            pollRef.current = null;
        }
    };

    const terminateRunnerSession = async () => {
        const sessionId = sessionIdRef.current;
        if (!sessionId) return;
        sessionIdRef.current = null;
        channelIdRef.current = null;
        try {
            await tenantApi(
                tenantSlug,
                `/ai/agents/${agentId}/test-call/${sessionId}`,
                { method: 'DELETE' },
            );
        } catch {
            // best-effort cleanup; runner will GC inactive rooms anyway.
        }
    };

    const handleStart = async () => {
        setErrorMsg(null);
        setStatus('connecting');
        setPostCallData(null);

        try {
            const response = await tenantApi<SessionResponse>(
                tenantSlug,
                `/ai/agents/${agentId}/test-call`,
                { method: 'POST' },
            );

            const session = response.data;
            sessionIdRef.current = session.session_id;
            channelIdRef.current = session.channel_id;
            setProvider(session.provider);

            if (session.provider !== 'livekit') {
                throw new Error(
                    `Transport "${session.provider}" no soportado todavía en esta UI. Configura TITO_RUNNERS_DEFAULT_TRANSPORT=livekit o el agente en livekit.`,
                );
            }

            if (!session.url || !session.access_token) {
                throw new Error(
                    'La respuesta del runner no incluye url o access_token.',
                );
            }

            const room = new Room({
                adaptiveStream: true,
                dynacast: true,
            });
            roomRef.current = room;

            room.on(RoomEvent.TrackSubscribed, (track: RemoteTrack) => {
                if (
                    track.kind === Track.Kind.Audio &&
                    audioElRef.current !== null
                ) {
                    track.attach(audioElRef.current);
                }
            });

            room.on(
                RoomEvent.TrackUnsubscribed,
                (
                    track: RemoteTrack,
                    _pub: RemoteTrackPublication,
                    _participant: RemoteParticipant,
                ) => {
                    track.detach();
                },
            );

            room.on(RoomEvent.Disconnected, () => {
                const channelId = channelIdRef.current;
                // If we were polling and the agent ended, don't reset state here
                if (status !== 'post-call') {
                    stopTimer();
                    stopPolling();
                    setStatus('idle');
                }
                void terminateRunnerSession();
            });

            await room.connect(session.url, session.access_token);
            await room.localParticipant.setMicrophoneEnabled(true);

            setStatus('connected');
            startTimer();
            startPolling();
        } catch (err) {
            const message =
                err instanceof TenantApiError
                    ? err.message
                    : err instanceof Error
                      ? err.message
                      : 'No se pudo iniciar la llamada';
            setErrorMsg(message);
            setStatus('error');
            stopPolling();
            void roomRef.current?.disconnect();
            roomRef.current = null;
            await terminateRunnerSession();
            stopTimer();
        }
    };

    const handleHangup = async () => {
        setStatus('ending');
        stopPolling();
        try {
            // Notify backend that user ended the session
            const channelId = channelIdRef.current;
            if (channelId) {
                await tenantApi(
                    tenantSlug,
                    `/api/ai/runner/sessions/${channelId}/user-ended`,
                    { method: 'POST' },
                ).catch(() => {});
            }
            await roomRef.current?.disconnect();
        } finally {
            roomRef.current = null;
            await terminateRunnerSession();
            stopTimer();
            setStatus('idle');
            setMuted(false);
            setPostCallData(null);
        }
    };

    const handleClosePostCall = () => {
        setStatus('idle');
        setPostCallData(null);
        sessionIdRef.current = null;
        channelIdRef.current = null;
    };

    const handleToggleMute = async () => {
        const room = roomRef.current;
        if (!room) return;
        const next = !muted;
        const publication = room.localParticipant
            .getTrackPublications()
            .find((p) => p.kind === Track.Kind.Audio);
        const track = publication?.track as LocalAudioTrack | undefined;
        if (track) {
            if (next) {
                await track.mute();
            } else {
                await track.unmute();
            }
        }
        setMuted(next);
    };

    const isLive = status === 'connected';
    const isBusy = status === 'connecting' || status === 'ending';

    return (
        <div className="flex flex-col gap-4 rounded-2xl border border-border bg-card p-6">
            <div className="flex items-center justify-between gap-4">
                <div>
                    <h3 className="text-base font-semibold">
                        Probar llamada con {agentName}
                    </h3>
                    <p className="text-sm text-muted-foreground">
                        Inicia una sesión WebRTC en vivo a través del runner.
                        {provider && (
                            <>
                                {' '}
                                Transporte: <strong>{provider}</strong>
                            </>
                        )}
                    </p>
                </div>
                <StatusPill status={status} elapsed={elapsed} />
            </div>

            <audio ref={audioElRef} autoPlay playsInline className="hidden" />

            <div className="flex flex-wrap items-center gap-3">
                {status === 'post-call' ? (
                    <>
                        <div className="flex-1">
                            <p className="text-sm font-medium">
                                Llamada Finalizada
                            </p>
                            <p className="text-xs text-muted-foreground">
                                El agente ha terminado la llamada.
                                {postCallData &&
                                    Object.keys(postCallData).length > 0 && (
                                        <> Datos de análisis disponibles.</>
                                    )}
                            </p>
                        </div>
                        <Button variant="outline" onClick={handleClosePostCall}>
                            Cerrar
                        </Button>
                    </>
                ) : !isLive ? (
                    <Button
                        onClick={handleStart}
                        disabled={isBusy}
                        className="gap-2"
                    >
                        <Phone className="size-4" />
                        {status === 'connecting'
                            ? 'Conectando...'
                            : 'Iniciar llamada'}
                    </Button>
                ) : (
                    <>
                        <Button
                            variant="destructive"
                            onClick={handleHangup}
                            disabled={isBusy}
                            className="gap-2"
                        >
                            <PhoneOff className="size-4" />
                            Colgar
                        </Button>
                        <Button
                            variant="outline"
                            onClick={handleToggleMute}
                            className="gap-2"
                        >
                            {muted ? (
                                <MicOff className="size-4" />
                            ) : (
                                <Mic className="size-4" />
                            )}
                            {muted ? 'Activar mic' : 'Silenciar'}
                        </Button>
                    </>
                )}
            </div>

            {errorMsg && (
                <div className="rounded-lg border border-destructive/30 bg-destructive/5 p-3 text-sm text-destructive">
                    {errorMsg}
                </div>
            )}
        </div>
    );
}

function StatusPill({
    status,
    elapsed,
}: {
    status: CallStatus;
    elapsed: number;
}) {
    const styles: Record<CallStatus, string> = {
        idle: 'bg-muted text-muted-foreground',
        connecting: 'bg-amber-500/10 text-amber-600',
        connected: 'bg-emerald-500/10 text-emerald-600',
        ending: 'bg-amber-500/10 text-amber-600',
        error: 'bg-destructive/10 text-destructive',
        'post-call': 'bg-blue-500/10 text-blue-600',
    };
    const labels: Record<CallStatus, string> = {
        idle: 'Inactivo',
        connecting: 'Conectando',
        connected: `En llamada · ${formatElapsed(elapsed)}`,
        ending: 'Finalizando',
        error: 'Error',
        'post-call': 'Llamada finalizada',
    };
    return (
        <span
            className={cn(
                'inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium',
                styles[status],
            )}
        >
            <span
                className={cn(
                    'size-2 rounded-full',
                    status === 'connected'
                        ? 'animate-pulse bg-emerald-500'
                        : status === 'connecting' || status === 'ending'
                          ? 'bg-amber-500'
                          : status === 'error' || status === 'post-call'
                            ? 'bg-destructive'
                            : 'bg-muted-foreground/50',
                )}
            />
            {labels[status]}
        </span>
    );
}

function formatElapsed(seconds: number): string {
    const mm = Math.floor(seconds / 60)
        .toString()
        .padStart(2, '0');
    const ss = (seconds % 60).toString().padStart(2, '0');
    return `${mm}:${ss}`;
}
