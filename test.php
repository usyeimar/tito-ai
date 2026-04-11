<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$request = Illuminate\Http\Request::create('/demo/dashboard', 'GET');
$user = App\Models\Tenant\Auth\Authentication\User::first();
$tenant = App\Models\Central\Tenancy\Tenant::first();
Stancl\Tenancy\Facades\Tenancy::initialize($tenant);
$request->setUserResolver(function() use ($user) { return $user; });
$request->setLaravelSession(app('session')->driver());
$request->session()->start();
$response = new Illuminate\Http\Response('OK');
$factory = app(Laravel\Passport\ApiTokenCookieFactory::class);
$middleware = new Laravel\Passport\Http\Middleware\CreateFreshApiToken($factory);
$result = $middleware->handle($request, function($req) use ($response) { return $response; }, 'tenant');
$cookies = $result->headers->getCookies();
echo json_encode(array_map(function($c) { return $c->getName(); }, $cookies));
