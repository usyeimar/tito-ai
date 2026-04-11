<?php

declare(strict_types=1);

namespace App\Services\Central\Support\OutboundEmails;

class ResendWebhookRoutingService
{
    /**
     * Extracts tenant routing information from webhook tags.
     */
    public function extractTenantRouting(array $payload): ?array
    {
        $tags = $payload['data']['tags'] ?? [];

        if (empty($tags)) {
            return null;
        }

        // Handle string list format (some providers)
        if (is_string($tags[0] ?? null)) {
            foreach ($tags as $tag) {
                if (str_contains($tag, 'tenant_id:')) {
                    return ['tenant_id' => str_replace('tenant_id:', '', $tag)];
                }
            }
        }

        // Handle key-value objects (Standard Resend format)
        foreach ($tags as $tag) {
            if (isset($tag['name']) && $tag['name'] === 'tenant_id') {
                return ['tenant_id' => $tag['value']];
            }

            // Handle direct key-value map
            if (isset($tag['tenant_id'])) {
                return ['tenant_id' => $tag['tenant_id']];
            }
        }

        return null;
    }
}
