<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = App\Models\Tenant\Auth\Authentication\User::first();
$tenant = App\Models\Central\Tenancy\Tenant::first();
Stancl\Tenancy\Facades\Tenancy::initialize($tenant);

$request = Illuminate\Http\Request::create('/demo/dashboard', 'GET');
$request->setUserResolver(function() use ($user) { return $user; });
$request->setLaravelSession(app('session')->driver());
$request->session()->start();

// Create cookie
$response = new Illuminate\Http\Response('OK');
$factory = app(Laravel\Passport\ApiTokenCookieFactory::class);
$middleware = new Laravel\Passport\Http\Middleware\CreateFreshApiToken($factory);
$result = $middleware->handle($request, function($req) use ($response) { return $response; }, 'tenant');

// Encrypt cookie
$encryptMiddleware = app(Illuminate\Cookie\Middleware\EncryptCookies::class);
$result = $encryptMiddleware->handle($request, function() use ($result) { return $result; });

$cookies = $result->headers->getCookies();
$tenantTokenCookie = $cookies[0]->getValue();
echo "Encrypted tenant_token length: " . strlen($tenantTokenCookie) . "\n";

// Test decryption
$apiRequest = Illuminate\Http\Request::create('/demo/api/ai/agents', 'POST');
$apiRequest->cookies->set('tenant_token', $tenantTokenCookie);

$guard = auth()->guard('tenant-api');
$guard->setRequest($apiRequest);
$authenticatedUser = $guard->user();
echo "Authenticated User: " . ($authenticatedUser ? $authenticatedUser->id : 'null') . "\n";

