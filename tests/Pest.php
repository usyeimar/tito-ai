<?php

use Tests\TenantTestCase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case Binding
|--------------------------------------------------------------------------
*/

pest()->extend(TestCase::class)->in('Feature/Central');
pest()->extend(TenantTestCase::class)->in('Feature/Tenant');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function assertHasValidationError($response, string $pointer): void
{
    $response->assertUnprocessable();
    $errors = $response->json('errors');

    $found = collect($errors)->contains(fn ($error) => ($error['source']['pointer'] ?? null) === $pointer);

    expect($found)->toBeTrue("Expected validation error for pointer '{$pointer}' not found.");
}
