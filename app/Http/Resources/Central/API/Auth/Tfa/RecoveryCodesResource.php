<?php

namespace App\Http\Resources\Central\API\Auth\Tfa;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecoveryCodesResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        if (array_key_exists('has_recovery_codes', $this->resource)) {
            return [
                'has_recovery_codes' => (bool) $this->resource['has_recovery_codes'],
            ];
        }

        $recoveryCodes = $this->resource['recovery_codes'] ?? [];
        $formattedCodes = [];

        if (is_array($recoveryCodes)) {
            foreach ($recoveryCodes as $entry) {
                if (is_array($entry) && array_key_exists('code', $entry)) {
                    $formattedCodes[] = [
                        'code' => (string) ($entry['code'] ?? ''),
                        'used_at' => $entry['used_at'] ?? null,
                    ];

                    continue;
                }

                if (is_string($entry)) {
                    $formattedCodes[] = [
                        'code' => $entry,
                        'used_at' => null,
                    ];
                }
            }
        }

        $response = [
            'recovery_codes' => $formattedCodes,
        ];

        if (array_key_exists('regenerated', $this->resource)) {
            $response['regenerated'] = (bool) $this->resource['regenerated'];
        }

        return $response;
    }
}
