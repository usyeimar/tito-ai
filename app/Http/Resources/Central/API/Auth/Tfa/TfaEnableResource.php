<?php

namespace App\Http\Resources\Central\API\Auth\Tfa;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TfaEnableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'secret' => $this->resource['secret'],
            'otpauth_url' => $this->resource['otpauth_url'],
        ];
    }
}
