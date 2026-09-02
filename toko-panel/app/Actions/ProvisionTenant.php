<?php

namespace App\Actions;

use App\Contracts\TenantDatabaseProvisioner;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProvisionTenant
{
    public function __construct(private readonly TenantDatabaseProvisioner $databaseProvisioner) {}

    /**
     * @throws Throwable
     */
    public function handle(Tenant $tenant): void
    {
        $tenant->loadMissing(['owner', 'rentalOrder']);
        $tenant->update(['provisioning_status' => 'provisioning']);

        try {
            $this->databaseProvisioner->provision($tenant);

            foreach ($this->domainsFor($tenant) as $domain) {
                $tenant->domains()->firstOrCreate(['domain' => $domain]);
            }

            $temporaryPassword = null;
            $ownerEmail = null;

            if ($tenant->rentalOrder !== null) {
                $temporaryPassword = Str::password(16, symbols: true);
                $ownerName = $tenant->owner->name;
                $ownerEmail = $tenant->owner->email;
                $ownerPhone = $tenant->rentalOrder->whatsapp;

                $tenant->run(function () use ($tenant, $temporaryPassword, $ownerName, $ownerEmail, $ownerPhone): void {
                    $now = now();
                    $permissions = ['access admin', 'manage products', 'manage orders', 'manage store settings', 'view reports'];

                    foreach ($permissions as $permission) {
                        DB::table('permissions')->updateOrInsert(
                            ['name' => $permission, 'guard_name' => 'web'],
                            ['updated_at' => $now, 'created_at' => $now],
                        );
                    }

                    DB::table('roles')->updateOrInsert(
                        ['name' => 'owner', 'guard_name' => 'web'],
                        ['updated_at' => $now, 'created_at' => $now],
                    );

                    DB::table('users')->updateOrInsert(
                        ['email' => $ownerEmail],
                        [
                            'name' => $ownerName,
                            'email_verified_at' => $now,
                            'password' => Hash::make($temporaryPassword),
                            'must_change_password' => true,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ],
                    );

                    $ownerId = DB::table('users')->where('email', $ownerEmail)->value('id');
                    $roleId = DB::table('roles')->where('name', 'owner')->where('guard_name', 'web')->value('id');
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id' => $roleId,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $ownerId,
                    ]);

                    $permissionIds = DB::table('permissions')->whereIn('name', $permissions)->where('guard_name', 'web')->pluck('id');
                    foreach ($permissionIds as $permissionId) {
                        DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
                    }

                    DB::table('store_settings')->updateOrInsert(
                        ['id' => 1],
                        [
                            'store_name' => $tenant->name,
                            'contact_email' => $ownerEmail,
                            'phone' => $ownerPhone,
                            'tagline' => 'Belanja mudah, aman, dan nyaman.',
                            'updated_at' => $now,
                            'created_at' => $now,
                        ],
                    );
                    DB::table('payment_gateways')->updateOrInsert(
                        ['code' => 'midtrans'],
                        [
                            'name' => 'Midtrans',
                            'instructions' => 'Pembayaran otomatis melalui kanal yang tersedia pada plan toko.',
                            'config' => json_encode(['provider' => 'midtrans']),
                            'is_active' => true,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ],
                    );
                });
            }

            $tenant->update(['provisioning_status' => 'active']);
            if ($tenant->rentalOrder !== null) {
                $tenant->rentalOrder->update([
                    'status' => 'ready',
                    'engine_login_email' => $ownerEmail,
                    'engine_temporary_password' => $temporaryPassword,
                ]);
            }
        } catch (Throwable $exception) {
            $tenant->update(['provisioning_status' => 'failed']);

            Log::error('Tenant provisioning failed.', [
                'tenant_id' => $tenant->getKey(),
                'subdomain' => $tenant->subdomain,
                'database_name' => $tenant->database_name,
                'engine_path' => config('tenancy.engine_path'),
                'exception' => $exception,
            ]);

            throw $exception;
        }
    }

    /** @return list<string> */
    private function domainsFor(Tenant $tenant): array
    {
        $domains = [$tenant->subdomain.'.'.config('tenancy.base_domain')];

        if ($tenant->custom_domain) {
            $domains[] = $tenant->custom_domain;
        }

        return array_values(array_unique(array_map('strtolower', $domains)));
    }
}
