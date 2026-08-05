<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $identifier = fake()->unique()->lexify('????????????');

        return [
            'name' => fake()->company(),
            'subdomain' => $identifier,
            'custom_domain' => null,
            'owner_user_id' => User::factory(),
            'database_name' => "tenant_{$identifier}",
            'provisioning_status' => 'pending',
            'store_status' => 'active',
        ];
    }
}
