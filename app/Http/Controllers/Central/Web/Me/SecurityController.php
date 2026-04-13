<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central\Web\Me;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;

class SecurityController extends Controller
{
    public function show(Request $request): Response
    {
        $user = $request->user();

        return inertia('me/security', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
