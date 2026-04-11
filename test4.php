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
$sessionToken = $request->session()->token();

$response = new Illuminate\Http\Response('OK');
$factory = app(Laravel\Passport\ApiTokenCookieFactory::class);
$middleware = new Laravel\Passport\Http\Middleware\CreateFreshApiToken($factory);
$result = $middleware->handle($request, function($req) use ($response) { return $response; }, 'tenant');

$encryptMiddleware = app(Illuminate\Cookie\Middleware\EncryptCookies::class);
$result = $encryptMiddleware->handle($request, function() use ($result) { return $result; });

$cookies = $result->headers->getCookies();
$tenantTokenCookie = $cookies[0]->getValue();

$apiRequest = Illuminate\Http\Request::create('/demo/api/ai/agents', 'POST');
$apiRequest->cookies->set('tenant_token', $tenantTokenCookie);
$apiRequest->headers->set('X-CSRF-TOKEN', $sessionToken);

// MANUALLY DO WHAT TOKEN GUARD DOES
$encrypter = app(Illuminate\Contracts\Encryption\Encrypter::class);
try {
    $decrypted = \Illuminate\Cookie\CookieValuePrefix::remove($encrypter->decrypt($tenantTokenCookie, false));
    echo "Decrypted JWT length: " . strlen($decrypted) . "\n";
    $jwt = \Firebase\JWT\JWT::decode($decrypted, new \Firebase\JWT\Key(\Laravel\Passport\Passport::tokenEncryptionKey($encrypter), 'HS256'));
    echo "Decoded JWT: " . json_encode($jwt) . "\n";
} catch (\Exception $e) {
    echo "Error decoding JWT: " . $e->getMessage() . "\n";
}
