<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $adminRole = Role::findOrCreate(UserRole::Admin->value, 'web');
        $ownerRole = Role::findOrCreate(UserRole::Owner->value, 'web');

        $admin = User::updateOrCreate(
            ['email' => (string) config('seeding.admin.email')],
            [
                'name' => 'Admin ScriptMedia',
                'email_verified_at' => now(),
                'password' => Hash::make((string) config('seeding.admin.password')),
            ],
        );
        $admin->syncRoles([$adminRole]);

        $owner = User::updateOrCreate(
            ['email' => (string) config('seeding.owner.email')],
            [
                'name' => 'Demo Owner',
                'email_verified_at' => now(),
                'password' => Hash::make((string) config('seeding.owner.password')),
            ],
        );
        $owner->syncRoles([$ownerRole]);

        $plan = Plan::updateOrCreate(
            ['name' => 'starter'],
            [
                'slug' => 'starter',
                'price_platform' => 1500000,
                'price_care_monthly' => 250000,
                'price_care_annual' => 2500000,
                'max_products' => 50,
                'max_payment_gateways' => 1,
                'content_request_quota' => 1,
                'support_sla_hours' => 24,
                'custom_domain_allowed' => false,
                'allow_realtime_shipping' => false,
                'allow_full_design_customization' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
        );

        $tenant = Tenant::updateOrCreate(
            ['subdomain' => 'demo'],
            [
                'name' => 'Demo Store',
                'owner_user_id' => $owner->id,
                'database_name' => 'tenant_demo',
                'provisioning_status' => 'active',
                'store_status' => 'active',
            ],
        );

        Subscription::updateOrCreate(
            ['tenant_id' => $tenant->id, 'status' => 'active'],
            [
                'plan_id' => $plan->id,
                'billing_cycle' => 'monthly',
                'current_period_start' => today()->startOfMonth(),
                'current_period_end' => today()->endOfMonth(),
                'next_billing_date' => today()->addMonth()->startOfMonth(),
                'pending_plan_id' => null,
            ],
        );
    }
}
