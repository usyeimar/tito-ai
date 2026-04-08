<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

class PublicUrlGenerator
{
    /**
     * @param  array<string, mixed>  $parameters
     */
    public function route(string $name, array $parameters = []): string
    {
        $publicBaseUrl = $this->publicBaseUrl();

        if ($publicBaseUrl === null) {
            return route($name, $parameters);
        }

        return $this->to(route($name, $parameters, false));
    }

    public function to(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL) !== false) {
            return $path;
        }

        $publicBaseUrl = $this->publicBaseUrl();

        if ($publicBaseUrl === null) {
            return URL::to($path);
        }

        return $publicBaseUrl.'/'.ltrim($path, '/');
    }

    public function publicBaseUrl(): ?string
    {
        $publicBaseUrl = trim((string) config('app.public_ingress_url', ''));

        if ($publicBaseUrl === '') {
            return null;
        }

        return rtrim($publicBaseUrl, '/');
    }
}
