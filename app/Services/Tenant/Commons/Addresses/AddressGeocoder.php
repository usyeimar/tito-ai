<?php

namespace App\Services\Tenant\Commons\Addresses;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Throwable;

class AddressGeocoder
{
    /**
     * Fill in lat/lng from address fields via Google Maps Geocoding API.
     *
     * Returns the array with lat/lng populated on success, or unchanged on failure.
     *
     * @param  array<string, mixed>  $fields
     * @return array<string, mixed>
     */
    public function geocode(array $fields): array
    {
        if (! $this->enabled()) {
            return $fields;
        }

        $query = $this->buildQuery($fields);
        if ($query === null) {
            return $fields;
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 200)
                ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $query,
                    'key' => (string) config('services.google_maps.api_key'),
                ]);

            if (! $response->ok()) {
                return $fields;
            }

            $payload = $response->json();
            if (! is_array($payload) || (string) Arr::get($payload, 'status') !== 'OK') {
                return $fields;
            }

            $result = Arr::get($payload, 'results.0');
            if (! is_array($result)) {
                return $fields;
            }

            $lat = Arr::get($result, 'geometry.location.lat');
            $lng = Arr::get($result, 'geometry.location.lng');

            if (is_numeric($lat)) {
                $fields['lat'] = (float) $lat;
            }

            if (is_numeric($lng)) {
                $fields['lng'] = (float) $lng;
            }

            return $fields;
        } catch (Throwable) {
            return $fields;
        }
    }

    public function enabled(): bool
    {
        return trim((string) config('services.google_maps.api_key')) !== '';
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function buildQuery(array $fields): ?string
    {
        $parts = array_filter([
            $this->normalize($fields['address_line'] ?? null),
            $this->normalize($fields['city'] ?? null),
            $this->normalize($fields['state_region'] ?? null),
            $this->normalize($fields['postal_code'] ?? null),
            $this->normalize($fields['country_code'] ?? null),
        ]);

        if ($parts === []) {
            return null;
        }

        return implode(', ', $parts);
    }

    private function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
