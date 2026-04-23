<?php

declare(strict_types=1);

namespace App\Models\Tenant\Agent;

use Database\Factories\Tenant\Agent\TrunkFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trunk extends Model
{
    /** @use HasFactory<TrunkFactory> */
    use HasFactory, HasUlids;

    protected $table = 'trunks';

    protected $fillable = [
        'agent_id',
        'name',
        'mode',
        'max_concurrent_calls',
        'codecs',
        'status',
        'inbound_auth',
        'routes',
        'sip_host',
        'sip_port',
        'register_config',
        'outbound',
    ];

    protected $casts = [
        'codecs' => 'array',
        'inbound_auth' => 'array',
        'routes' => 'array',
        'register_config' => 'array',
        'outbound' => 'array',
        'max_concurrent_calls' => 'integer',
        'sip_port' => 'integer',
    ];

    public const MODE_INBOUND = 'inbound';

    public const MODE_REGISTER = 'register';

    public const MODE_OUTBOUND = 'outbound';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_SUSPENDED = 'suspended';

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isInbound(): bool
    {
        return $this->mode === self::MODE_INBOUND;
    }

    public function isRegister(): bool
    {
        return $this->mode === self::MODE_REGISTER;
    }

    public function isOutbound(): bool
    {
        return $this->mode === self::MODE_OUTBOUND;
    }

    /**
     * Get the route that matches a given extension pattern.
     */
    public function resolveRoute(string $extension): ?array
    {
        $routes = $this->routes ?? [];

        foreach ($routes as $route) {
            if (! ($route['enabled'] ?? true)) {
                continue;
            }

            $pattern = $route['pattern'] ?? '';

            if ($pattern === '*' || $pattern === $extension) {
                return $route;
            }

            // Support dialplan-style patterns like _X. or _XXXX
            if (str_starts_with($pattern, '_')) {
                $regex = $this->patternToRegex($pattern);
                if (preg_match($regex, $extension)) {
                    return $route;
                }
            }
        }

        return null;
    }

    /**
     * Convert a dialplan pattern to a regex.
     */
    private function patternToRegex(string $pattern): string
    {
        $regex = str_replace(
            ['_X.', '_Z.', '_N.', '_.', '_X', '_Z', '_N'],
            ['[0-9]', '[2-9]', '[2-9]', '.', '[0-9]$', '[2-9]$', '[2-9]$'],
            $pattern
        );

        return '/^'.substr($regex, 1).'$/';
    }

    /**
     * Get the Redis key for this trunk.
     */
    public function getRedisKey(): string
    {
        return 'trunk:'.$this->id;
    }

    /**
     * Get the Redis index key for the current tenant.
     */
    public static function getIndexKey(?string $tenantSlug = null): string
    {
        return 'trunk:index:'.($tenantSlug ?? tenant()?->slug ?? 'default');
    }
}
