<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\API\Public\Widget;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Agent\Agent;
use Illuminate\Http\JsonResponse;

final class WidgetConfigController extends Controller
{
    /**
     * Get web widget configuration for an agent by slug
     */
    public function getWebWidgetConfig(string $agentSlug): JsonResponse
    {
        // Get the agent by slug
        $agent = Agent::where('slug', $agentSlug)->firstOrFail();

        // Get the active web-widget deployment
        $deployment = $agent->deployments()
            ->where('channel', 'web-widget')
            ->where('enabled', true)
            ->where('status', 'active')
            ->latest('version')
            ->first();

        if (! $deployment) {
            return response()->json([
                'error' => 'No active web widget deployment found for this agent',
            ], 404);
        }

        // Extract widget-specific configuration
        $widgetConfig = $deployment->config['widget'] ?? [];
        $livekitConfig = $deployment->config['livekit'] ?? [];
        $privacyConfig = $deployment->config['privacy'] ?? [];

        // Prepare response with only necessary public configuration
        $responseData = [
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'slug' => $agent->slug,
            ],
            'deployment' => [
                'version' => $deployment->version,
                'deployed_at' => $deployment->deployed_at,
            ],
            'widget' => array_merge(
                $widgetConfig,
                [
                    'agentId' => $agent->id,
                    'deploymentVersion' => $deployment->version,
                    'wsUrl' => config('livekit.ws_url'),
                    // Generate a temporary token or use a public endpoint for token generation
                    'tokenEndpoint' => route('public.widget-token.generate', ['agentSlug' => $agent->slug]),
                ]
            ),
            'livekit' => $livekitConfig,
            'privacy' => $privacyConfig,
        ];

        // Cache the response for 5 minutes to reduce database load
        return response()->json($responseData)
            ->setSharedMaxAge(300)
            ->setMaxAge(300);
    }

    /**
     * Get SIP widget configuration for an agent by slug
     */
    public function getSipWidgetConfig(string $agentSlug): JsonResponse
    {
        // Get the agent by slug
        $agent = Agent::where('slug', $agentSlug)->firstOrFail();

        // Get the active SIP deployment
        $deployment = $agent->deployments()
            ->where('channel', 'sip')
            ->where('enabled', true)
            ->where('status', 'active')
            ->latest('version')
            ->first();

        if (! $deployment) {
            return response()->json([
                'error' => 'No active SIP deployment found for this agent',
            ], 404);
        }

        // Extract SIP-specific configuration
        $sipConfig = $deployment->config['sip'] ?? [];

        // Prepare response with only necessary public configuration
        $responseData = [
            'agent' => [
                'id' => $agent->id,
                'name' => $agent->name,
                'slug' => $agent->slug,
            ],
            'deployment' => [
                'version' => $deployment->version,
                'deployed_at' => $deployment->deployed_at,
            ],
            'sip' => array_merge(
                $sipConfig,
                [
                    'agentId' => $agent->id,
                    'deploymentVersion' => $deployment->version,
                    'sipServer' => config('sip.server'),
                    'sipPort' => config('sip.port'),
                    'sipTransport' => config('sip.transport', 'udp'),
                    'authRealm' => config('sip.auth_realm'),
                ]
            ),
        ];

        // Cache the response for 5 minutes to reduce database load
        return response()->json($responseData)
            ->setSharedMaxAge(300)
            ->setMaxAge(300);
    }
}
