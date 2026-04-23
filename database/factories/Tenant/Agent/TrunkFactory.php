<?php

declare(strict_types=1);

namespace Database\Factories\Tenant\Agent;

use App\Models\Tenant\Agent\Agent;
use App\Models\Tenant\Agent\Trunk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trunk>
 */
class TrunkFactory extends Factory
{
    protected $model = Trunk::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'agent_id' => null,
            'name' => fake()->words(3, true).' Trunk',
            'mode' => Trunk::MODE_INBOUND,
            'max_concurrent_calls' => fake()->numberBetween(5, 50),
            'codecs' => ['ulaw', 'alaw'],
            'status' => Trunk::STATUS_ACTIVE,
            'inbound_auth' => [
                'auth_type' => 'ip',
                'allowed_ips' => ['0.0.0.0/0'],
            ],
            'routes' => [
                [
                    'pattern' => '*',
                    'agent_id' => null,
                    'priority' => 0,
                    'enabled' => true,
                ],
            ],
            'sip_host' => fake()->domainName(),
            'sip_port' => 5060,
            'register_config' => null,
            'outbound' => null,
        ];
    }

    public function inbound(): static
    {
        return $this->state(fn () => ['mode' => Trunk::MODE_INBOUND]);
    }

    public function register(): static
    {
        return $this->state(fn () => [
            'mode' => Trunk::MODE_REGISTER,
            'register_config' => [
                'server' => 'sip.example.com',
                'port' => 5060,
                'username' => fake()->userName(),
                'password' => fake()->password(8, 16),
                'register_interval' => 60,
            ],
        ]);
    }

    public function outbound(): static
    {
        return $this->state(fn () => [
            'mode' => Trunk::MODE_OUTBOUND,
            'outbound' => [
                'trunk_name' => fake()->company(),
                'server' => 'sip.example.com',
                'port' => 5060,
                'username' => fake()->userName(),
                'password' => fake()->password(8, 16),
                'caller_id' => fake()->phoneNumber(),
            ],
        ]);
    }

    public function withAgent(Agent $agent): static
    {
        return $this->state(fn () => [
            'agent_id' => $agent->id,
            'routes' => [
                [
                    'pattern' => '*',
                    'agent_id' => (string) $agent->id,
                    'priority' => 0,
                    'enabled' => true,
                ],
            ],
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => Trunk::STATUS_ACTIVE]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Trunk::STATUS_INACTIVE]);
    }
}
