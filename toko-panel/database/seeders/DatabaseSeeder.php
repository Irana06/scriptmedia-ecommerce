<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\InvoiceItem;
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

        $this->call(PlanSeeder::class);

        /*
         * One tenant per plan, matching the three demo storefronts in toko-engine,
         * so the panel shows a client on Starter, Standard and Pro at once instead
         * of a single generic store. The Starter tenant keeps the tenant_demo
         * database name that toko-engine's CENTRAL_TENANT_DATABASE points at.
         */
        $demoTenants = [
            ['plan' => 'starter', 'name' => 'Kedai Rona', 'subdomain' => 'kedai-rona', 'database' => 'tenant_demo', 'cycle' => 'monthly'],
            ['plan' => 'standard', 'name' => 'Shicomp Store', 'subdomain' => 'shicomp', 'database' => 'tenant_shicomp', 'cycle' => 'monthly'],
            ['plan' => 'pro', 'name' => 'Nara Atelier', 'subdomain' => 'nara-atelier', 'database' => 'tenant_nara_atelier', 'cycle' => 'annual'],
        ];

        foreach ($demoTenants as $demoTenant) {
            $plan = Plan::query()->where('name', $demoTenant['plan'])->sole();

            // Matched on the database, which is the identity toko-engine resolves
            // against, so re-seeding renames an existing tenant instead of
            // colliding with its unique database name.
            $tenant = Tenant::updateOrCreate(
                ['database_name' => $demoTenant['database']],
                [
                    'name' => $demoTenant['name'],
                    'subdomain' => $demoTenant['subdomain'],
                    'owner_user_id' => $owner->id,
                    'provisioning_status' => 'active',
                    'store_status' => 'active',
                ],
            );

            $periodStart = today()->startOfMonth();
            $periodEnd = $demoTenant['cycle'] === 'annual'
                ? $periodStart->copy()->addYear()->subDay()
                : $periodStart->copy()->endOfMonth();

            $subscription = Subscription::updateOrCreate(
                ['tenant_id' => $tenant->id, 'status' => 'active'],
                [
                    'plan_id' => $plan->id,
                    'billing_cycle' => $demoTenant['cycle'],
                    'current_period_start' => $periodStart,
                    'current_period_end' => $periodEnd,
                    'next_billing_date' => $periodEnd->copy()->addDay(),
                    'pending_plan_id' => null,
                ],
            );

            $this->seedPaidInvoice($subscription, $plan);
        }
    }

    /**
     * The invoice for the period the tenant is already in, marked paid.
     *
     * Without it the billing screens read as empty, which makes a working panel
     * look broken. Recurring invoices after this one come from the scheduler.
     */
    private function seedPaidInvoice(Subscription $subscription, Plan $plan): void
    {
        $isAnnual = $subscription->billing_cycle === 'annual';
        $multiplier = $isAnnual ? 10 : 1;
        $platform = (float) $plan->price_platform * $multiplier;
        $care = (float) $plan->price_care_monthly * $multiplier;

        $invoice = Invoice::updateOrCreate(
            [
                'subscription_id' => $subscription->id,
                'billing_period_start' => $subscription->current_period_start,
            ],
            [
                'tenant_id' => $subscription->tenant_id,
                'invoice_number' => 'INV-'.$subscription->current_period_start->format('Ym').'-'.str_pad((string) $subscription->tenant_id, 4, '0', STR_PAD_LEFT),
                'status' => 'paid',
                'billing_period_end' => $subscription->current_period_end,
                'subtotal_platform' => $platform,
                'subtotal_care' => $care,
                'total' => $platform + $care,
                'due_date' => $subscription->current_period_start,
                'paid_at' => $subscription->current_period_start,
            ],
        );

        $items = [
            'Sewa Platform — Plan '.str($plan->name)->title() => $platform,
            'Web Care '.($isAnnual ? 'Tahunan' : 'Bulanan').' — Plan '.str($plan->name)->title() => $care,
        ];

        foreach ($items as $description => $amount) {
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $invoice->id, 'description' => $description],
                ['amount' => $amount],
            );
        }
    }
}
