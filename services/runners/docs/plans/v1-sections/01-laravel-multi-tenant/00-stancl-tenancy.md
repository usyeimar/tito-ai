## 0. Laravel Multi-Tenant con Stancl Tenancy (Ecosistema de Microservicios)

Esta sección detalla cómo implementar el modo multi-tenant usando **Stancl Tenancy for Laravel** (paquete especializado), integrándose con el ecosistema de microservicios existente.

### 0.1 ¿Qué es Stancl Tenancy?

**Stancl Tenancy** (`stancl/tenancy`) es un paquete Laravel que proporciona:
- Multi-tenancy con múltiples schemas de PostgreSQL
- Identificación por subdomain, dominio, o header
- Migraciones automáticas por tenant
- Configuración de filesystem separada por tenant
- Integración con Sanctum para autenticación

**Instalación:**
```bash
composer require stancl/tenancy
php artisan tenancy:install
```

### 0.2 Arquitectura Multi-Tenant con Stancl Tenancy

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│                 LARAVEL MULTI-TENANT - STANCL TENANCY                              │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                      │
│  ┌───────────────────────────────────────────────────────────────────────────────┐   │
│  │                    LARAVEL API GATEWAY                                         │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        │   │
│  │  │  Routing    │  │   Auth JWT  │  │  Stancl     │  │  Rate       │        │   │
│  │  │  (based on  │  │  (Sanctum)  │  │  Tenant     │  │  Limiting   │        │   │
│  │  │   subdomain)│  │             │  │  Middleware │  │             │        │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘        │   │
│  └───────────────────────────────────────────────────────────────────────────────┘   │
│                                        │                                            │
│         ┌──────────────────────────────┼──────────────────────────────┐            │
│         │         TENANT ISOLATION      │                              │            │
│         ▼                              ▼                              ▼            │
│  ┌─────────────┐              ┌─────────────┐              ┌─────────────┐          │
│  │   Tenant A  │              │   Tenant B  │              │   Tenant N  │          │
│  │  Schema DB  │              │  Schema DB  │              │  Schema DB  │          │
│  │  tenant_a   │              │  tenant_b   │              │  tenant_n   │          │
│  └─────────────┘              └─────────────┘              └─────────────┘          │
│         │                              │                              │            │
│         └──────────────────────────────┴──────────────────────────────┘            │
│                                    │                                               │
│                                    ▼                                               │
│  ┌───────────────────────────────────────────────────────────────────────────────┐   │
│  │                    POSTGRESQL (Single Instance)                               │   │
│  │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐            │   │
│  │  │  tenant_a   │ │  tenant_b   │ │  tenant_n   │ │   public    │            │   │
│  │  │  (schema)   │ │  (schema)   │ │  (schema)   │ │  (shared)   │            │   │
│  │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘            │   │
│  └───────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                      │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

### 0.3 Instalación y Configuración

#### Instalación

```bash
# Instalar paquete
composer require stancl/tenancy

# Publicar configuración
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider"

# Instalar migrations y configuración
php artisan tenancy:install
```

#### Configuración Principal

```php
// config/tenancy.php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    */
    'tenant_model' => \App\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant Database Manager
    |--------------------------------------------------------------------------
    |
    | Database manager strategy. Use 'prefix' for MySQL/MariaDB,
    | 'schema' for PostgreSQL.
    |
    */
    'database_manager' => \Stancl\Tenancy\DatabaseManagers\PostgreSQLSchemaManager::class,

    /*
    |--------------------------------------------------------------------------
    | Tenant Connection
    |--------------------------------------------------------------------------
    */
    'tenant_connection' => 'pgsql',

    /*
    |--------------------------------------------------------------------------
    | Domain Parsing
    |--------------------------------------------------------------------------
    |
    | How to parse the request to identify the tenant.
    | Options: subdomain, domain, header
    |
    */
    'domain_parsing' => [
        'driver' => 'subdomain',  // empresa1.tito.ai
        'prefix' => 'tenant_',     // prefix para el schema: tenant_empresa1
        // También soportado: 'domain' o 'header'
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Settings
    |--------------------------------------------------------------------------
    */
    'migrations' => [
        'paths' => [
            database_path('migrations/tenant'),
        ],
        'exclude_paths' => [
            database_path('migrations/central'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    */
    'features' => [
        // tenancy: db, storage, routing, initialization, commands, events
        \Stancl\Tenancy\Features\TenancyBootstrap::class => true,
        \Stancl\Tenancy\Features\TenantConfig::class => true,
        \Stancl\Tenancy\Features\TenantRoutes::class => true,
        \Stancl\Tenancy\Features\TenantStorage::class => true,
        \Stancl\Tenancy\Features\DomainIdentification::class => true,
        \Stancl\Tenancy\Features\SilentTenancyPrevention::class => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Queue job to dispatch for tenant initialization, migration etc.
    |
    */
    'queue' => [
        'enable' => true,
        'connection' => 'redis',
        'queue' => 'tenancy',
    ],
];
```

```php
// config/database.php

return [
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'titoai_platform'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',  // Schema por defecto
            'sslmode' => env('DB_SSL_MODE', 'prefer'),
        ],
    ],
    
    'migrations' => 'migrations',
];
```

#### Modelo Tenant

```php
// app/Models/Tenant.php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends BaseTenant
{
    protected $fillable = [
        'id',
        'name',
        'slug',
    ];
    
    // Timestamps automáticamente manejados por Stancl
    // created_at y updated_at automáticos
    
    // Relaciones
    public function networks(): HasMany
    {
        return $this->hasMany(SipNetwork::class, 'tenant_id');
    }
    
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }
    
    // Custom attributes
    public function getDomainAttribute(): string
    {
        return "{$this->slug}.tito.ai";
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

### 0.4 Estructura de Base de Datos

#### Schema Public (Compartido - Tablas Centrales)

```sql
-- Schema: public (usado por Stancl para metadata de tenants)
-- Tablas de Stancl Tenancy (automáticas)

-- tenant (creada por Stancl)
CREATE TABLE tenants (
    id VARCHAR(255) PRIMARY KEY,  -- UUID o slug
    name VARCHAR(255) NOT NULL,
    data JSONB DEFAULT '{}',       -- datos custom (plan_id, etc)
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Tablas propias adicionales en public
CREATE TABLE plans (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) NOT NULL,
    description TEXT,
    max_users INTEGER,
    max_networks INTEGER,
    max_concurrent_calls INTEGER,
    max_calls_per_second INTEGER,
    features JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE domains (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id VARCHAR(255) REFERENCES tenants(id) ON DELETE CASCADE,
    domain VARCHAR(255) UNIQUE NOT NULL,
    is_primary BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW()
);
```

#### Schema por Tenant (Automático)

```sql
-- Schema: tenant_{slug} (creado automáticamente por Stancl)
-- Se crean cuando se provisiona un nuevo tenant

-- Redes SIP del tenant
CREATE TABLE sip_networks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    cidr CIDR NOT NULL,
    domain VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    config JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Peers/Extensiones
CREATE TABLE peers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    network_id UUID REFERENCES sip_networks(id),
    extension VARCHAR(20) NOT NULL,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    secret VARCHAR(255) NOT NULL,
    caller_id VARCHAR(100),
    status VARCHAR(50) DEFAULT 'offline',
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(network_id, extension)
);

-- Agentes
CREATE TABLE agents (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    network_id UUID REFERENCES sip_networks(id),
    name VARCHAR(255) NOT NULL,
    agent_type VARCHAR(50) NOT NULL,
    mode VARCHAR(50) DEFAULT 'both',
    ai_config_id UUID,
    status VARCHAR(50) DEFAULT 'available',
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Colas
CREATE TABLE queues (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    network_id UUID REFERENCES sip_networks(id),
    name VARCHAR(100) NOT NULL,
    strategy VARCHAR(50) DEFAULT 'ring_all',
    timeout INTEGER DEFAULT 30,
    max_wait_time INTEGER DEFAULT 300,
    config JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Trunks
CREATE TABLE trunks (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    network_id UUID REFERENCES sip_networks(id),
    name VARCHAR(100) NOT NULL,
    mode VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    config JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Rutas del dialplan
CREATE TABLE routes (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    network_id UUID REFERENCES sip_networks(id),
    name VARCHAR(100) NOT NULL,
    priority INTEGER DEFAULT 0,
    match_pattern VARCHAR(255) NOT NULL,
    destination_type VARCHAR(50) NOT NULL,
    destination_id UUID NOT NULL,
    config JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Llamadas (historial)
CREATE TABLE calls (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    network_id UUID REFERENCES sip_networks(id),
    agent_id UUID REFERENCES agents(id),
    call_type VARCHAR(50) NOT NULL,
    direction VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    from_number VARCHAR(50),
    to_number VARCHAR(50),
    room_name VARCHAR(255),
    started_at TIMESTAMP,
    answered_at TIMESTAMP,
    ended_at TIMESTAMP,
    duration_seconds INTEGER,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP DEFAULT NOW()
);
```

### 0.5 Migraciones

#### Migraciones Central (Schema Public)

```php
// database/migrations/central/2024_01_01_000001_create_plans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('max_users')->default(10);
            $table->integer('max_networks')->default(1);
            $table->integer('max_concurrent_calls')->default(10);
            $table->integer('max_calls_per_second')->default(5);
            $table->jsonb('features')->default('{}');
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
```

#### Migraciones por Tenant

```php
// database/migrations/tenant/2024_01_01_000001_create_tenant_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sip_networks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->index();
            $table->string('name');
            $table->string('slug');
            $table->cidr('cidr');
            $table->string('domain');
            $table->string('status')->default('active');
            $table->jsonb('config')->default('{}');
            $table->timestamps();
        });
        
        Schema::create('peers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('network_id')->index();
            $table->string('extension');
            $table->string('name');
            $table->string('username');
            $table->string('secret');  // encrypted
            $table->string('caller_id')->nullable();
            $table->string('status')->default('offline');
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
            $table->unique(['network_id', 'extension']);
        });
        
        Schema::create('agents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('network_id')->index();
            $table->string('name');
            $table->string('agent_type');
            $table->string('mode')->default('both');
            $table->uuid('ai_config_id')->nullable();
            $table->string('status')->default('available');
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
        });
        
        Schema::create('queues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('network_id')->index();
            $table->string('name');
            $table->string('strategy')->default('ring_all');
            $table->integer('timeout')->default(30);
            $table->integer('max_wait_time')->default(300);
            $table->jsonb('config')->default('{}');
            $table->timestamps();
        });
        
        Schema::create('trunks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('network_id')->index();
            $table->string('name');
            $table->string('mode');
            $table->string('status')->default('active');
            $table->jsonb('config')->default('{}');
            $table->timestamps();
        });
        
        Schema::create('routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('network_id')->index();
            $table->string('name');
            $table->integer('priority')->default(0);
            $table->string('match_pattern');
            $table->string('destination_type');
            $table->uuid('destination_id');
            $table->jsonb('config')->default('{}');
            $table->timestamps();
        });
        
        Schema::create('calls', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('network_id')->index();
            $table->uuid('agent_id')->nullable();
            $table->string('call_type');
            $table->string('direction');
            $table->string('status');
            $table->string('from_number')->nullable();
            $table->string('to_number')->nullable();
            $table->string('room_name')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
            
            $table->index('status');
            $table->index('started_at');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('calls');
        Schema::dropIfExists('routes');
        Schema::dropIfExists('trunks');
        Schema::dropIfExists('queues');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('peers');
        Schema::dropIfExists('sip_networks');
    }
};
```

### 0.6 Rutas y Controllers

#### Rutas con Tenancy

```php
// routes/api.php

use Stancl\Tenancy\Routing\Middleware;

Route::middleware([
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    
    // Networks
    Route::apiResource('networks', NetworkController::class);
    
    // Peers
    Route::apiResource('networks.peers', PeerController::class);
    
    // Agents
    Route::apiResource('networks.agents', AgentController::class);
    
    // Queues
    Route::apiResource('networks.queues', QueueController::class);
    
    // Trunks
    Route::apiResource('networks.trunks', TrunkController::class);
    
    // Routes
    Route::apiResource('networks.routes', RouteController::class);
    
    // Calls
    Route::get('calls', [CallController::class, 'index']);
    Route::post('calls', [CallController::class, 'store']);
    Route::get('calls/{call}', [CallController::class, 'show']);
    Route::delete('calls/{call}', [CallController::class, 'destroy']);
});
```

#### Controller con Acceso a Tenant

```php
// app/Http/Controllers/Api/V1/AgentController.php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Agent;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class AgentController extends Controller
{
    public function index()
    {
        // Ya está limitado al tenant actual automáticamente
        $agents = Agent::with('network')
            ->orderBy('name')
            ->paginate();
            
        return response()->json($agents);
    }
    
    public function store(Request $request)
    {
        // El tenant_id se inyecta automáticamente si usas el trait
        $validated = $request->validate([
            'network_id' => 'required|uuid|exists:sip_networks,id',
            'name' => 'required|string|max:255',
            'agent_type' => 'required|in:ai_voice,human,hybrid',
            'mode' => 'inbound|outbound|both',
        ]);
        
        $agent = Agent::create([
            ...$validated,
            'tenant_id' => tenant()->id,  // Inyectado automáticamente
        ]);
        
        return response()->json($agent, 201);
    }
    
    public function show(Agent $agent)
    {
        // Scope automático: solo puede ver agentes de su tenant
        return response()->json($agent->load('network', 'sessions'));
    }
    
    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'status' => 'in:available,on_call,away,offline',
        ]);
        
        $agent->update($validated);
        
        return response()->json($agent);
    }
    
    public function destroy(Agent $agent)
    {
        $agent->delete();
        
        return response()->json(null, 204);
    }
}
```

### 0.7 Integración con Microservicios

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│               LARAVEL + STANCL TENANCY + ECOSISTEMA MICROSERVICIOS                  │
├─────────────────────────────────────────────────────────────────────────────────────┤
│                                                                                      │
│  ┌───────────────────────────────────────────────────────────────────────────────┐   │
│  │              LARAVEL GATEWAY (Stancl Tenancy)                                 │   │
│  │  - Routing por subdomain: empresa1.tito.ai                                   │   │
│  │  - Tenant automatic en cada request                                           │   │
│  │  - DB schema dinámica por tenant                                              │   │
│  │  - Rate limiting por tenant                                                   │   │
│  └───────────────────────────────────────────────────────────────────────────────┘   │
│                                    │                                                │
│         ┌──────────────────────────┼──────────────────────────┐                    │
│         │                          │                          │                    │
│         ▼                          ▼                          ▼                    │
│  ┌─────────────┐           ┌─────────────┐           ┌─────────────┐              │
│  │  RUNNERS    │           │  SBC/SIP    │           │  LIVEKIT   │              │
│  │  Service    │           │  Service    │           │  Service    │              │
│  │  (Python)   │           │  (Kamailio) │           │  (LiveKit)  │              │
│  │             │           │             │           │             │              │
│  │ ┌─────────┐ │           │ ┌─────────┐ │           │ ┌─────────┐ │              │
│  │ │ gRPC/HTTP│ │           │ │  Redis  │ │           │ │  HTTP   │ │              │
│  │ └─────────┘ │           │ │  Pub/Sub│ │           │ └─────────┘ │              │
│  └─────────────┘           └─────────────┘           └─────────────┘              │
│         │                          │                          │                    │
│         └──────────────────────────┴──────────────────────────┘                    │
│                                    │                                                │
│                                    ▼                                                │
│  ┌───────────────────────────────────────────────────────────────────────────────┐   │
│  │                    POSTGRESQL (Múltiples Schemas)                            │   │
│  │   public (tenants, plans, domains) │ tenant_empresa1 │ tenant_empresa2         │   │
│  └───────────────────────────────────────────────────────────────────────────────┘   │
│                                                                                      │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

#### Comunicación con Runners (Python)

```php
// app/Services/RunnerService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Stancl\Tenancy\Facades\Tenancy;

class RunnerService
{
    protected string $runnerBaseUrl;
    
    public function __construct()
    {
        $this->runnerBaseUrl = config('services.runners.url');
    }
    
    public function startSession(string $agentId, array $context = []): string
    {
        $tenant = tenant();
        
        $response = Http::post("{$this->runnerBaseUrl}/api/v1/sessions", [
            'agent_id' => $agentId,
            'tenant_id' => $tenant->id,
            'network_id' => $context['network_id'] ?? null,
            'context' => $context,
            'metadata' => [
                'tenant_slug' => $tenant->slug,
                'domain' => $tenant->domain,
            ],
        ]);
        
        $response->throwIfFailed();
        
        return $response->json('session_id');
    }
    
    public function stopSession(string $sessionId): void
    {
        Http::delete("{$this->runnerBaseUrl}/api/v1/sessions/{$sessionId}")
            ->throwIfFailed();
    }
    
    public function getSessionStatus(string $sessionId): array
    {
        $response = Http::get("{$this->runnerBaseUrl}/api/v1/sessions/{$sessionId}");
        
        return $response->json();
    }
}
```

#### Comunicación con SBC (Redis Pub/Sub)

```php
// app/Services/SBCService.php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Stancl\Tenancy\Facades\Tenancy;

class SBCService
{
    public function routeCall(string $destination): array
    {
        $tenant = tenant();
        
        // Publicar evento para Kamailio
        Redis::publish('sbc:route', json_encode([
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'destination' => $destination,
            'timestamp' => now()->toIso8601String(),
        ]));
        
        return [
            'status' => 'queued',
            'tenant' => $tenant->slug,
        ];
    }
    
    public function registerTrunk(string $trunkId, array $config): void
    {
        $tenant = tenant();
        
        Redis::hset("trunk:{$tenant->id}", $trunkId, json_encode($config));
    }
    
    public function getTrunkStatus(string $trunkId): ?array
    {
        $tenant = tenant();
        
        $data = Redis::hget("trunk:{$tenant->id}", $trunkId);
        
        return $data ? json_decode($data, true) : null;
    }
}
```

### 0.8 Comandos de Gestión

```bash
# Crear un nuevo tenant
php artisan tenant:create empresa1 --domain=empresa1.tito.ai

# Listar tenants
php artisan tenant:list

# Migrar tenant específico
php artisan tenant:migrate empresa1

# Seed tenant específico
php artisan tenant:seed empresa1 --class=Database\\Seeders\\Tenant\\DefaultData

# Eliminar tenant
php artisan tenant:delete empresa1

# Enter migration (ejecutar migrations pendientes en todos los tenants)
php artisan tenant:migrate
```

### 0.9 Middleware y Rate Limiting

```php
// app/Http/Middleware/TenancyRateLimit.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class TenancyRateLimit
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant();
        
        if ($tenant && $tenant->plan) {
            $limit = $tenant->plan->max_calls_per_second ?? 10;
            $key = 'rate_limit:' . $tenant->id;
            
            // Usar Laravel's rate limiter
            if (rate_limit($key, $limit, 60)->tooManyAttempts()) {
                return response()->json([
                    'error' => 'Rate limit exceeded',
                    'retry_after' => rate_limit($key, $limit, 60)->availableIn(),
                ], 429);
            }
        }
        
        return $next($request);
    }
}
```

```php
// app/Http/Kernel.php

protected $middlewareGroups = [
    'api' => [
        \Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain::class,
        \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,
        \App\Http\Middleware\TenancyRateLimit::class,
        // ... otros middleware
    ],
];
```

### 0.10 Consideraciones de Seguridad

| Aspecto | Implementación |
|---------|----------------|
| **Aislamiento de DB** | Schema separado por tenant (PostgreSQL) |
| **Identificación** | Subdomain + validación de dominio en tabla `domains` |
| **Credenciales** | Encriptar secrets de peers con AES-256 |
| **Rate Limiting** | Por plan del tenant (configurable) |
| **Backups** | Backup por schema individual |
| **Logs** | Agregar `tenant_id` en cada entrada |

### 0.11 Modeloss Eloquent (Tenant-Aware)

```php
// app/Models/Tenant/SipNetwork.php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Traits\HasTenants;

class SipNetwork extends Model
{
    use HasTenants;  // Trait de Stancl para automatically inject tenant_id
    
    public $timestamps = true;
    
    protected $fillable = [
        'name',
        'slug',
        'cidr',
        'domain',
        'status',
        'config',
    ];
    
    protected $casts = [
        'config' => 'array',
        'cidr' => 'array',  // Para PostgreSQL CIDR
    ];
    
    // Tenant automáticamente incluido por el trait
    public $tenants = ['tenant_id'];
    
    public function peers(): HasMany
    {
        return $this->hasMany(Peer::class, 'network_id');
    }
    
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'network_id');
    }
    
    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class, 'network_id');
    }
    
    public function trunks(): HasMany
    {
        return $this->hasMany(Trunk::class, 'network_id');
    }
}
```

```php
// app/Models/Tenant/Peer.php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Traits\HasTenants;

class Peer extends Model
{
    use HasTenants;
    
    protected $fillable = [
        'network_id',
        'extension',
        'name',
        'username',
        'secret',
        'caller_id',
        'status',
        'metadata',
    ];
    
    protected $casts = [
        'metadata' => 'array',
    ];
    
    public $tenants = ['tenant_id'];
    
    public function network(): BelongsTo
    {
        return $this->belongsTo(SipNetwork::class, 'network_id');
    }
    
    public function getSecretAttribute($value)
    {
        // Decrypt cuando se lee
        return $value ? decrypt($value) : null;
    }
    
    public function setSecretAttribute($value)
    {
        // Encrypt cuando se guarda
        $this->attributes['secret'] = encrypt($value);
    }
}
```

---

## 1. Arquitectura SIP (PSTN)
