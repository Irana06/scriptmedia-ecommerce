<?php

namespace App\Actions;

use App\Contracts\TenantDatabaseProvisioner;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProvisionTenant
{
    public function __construct(private readonly TenantDatabaseProvisioner $databaseProvisioner) {}

    /**
     * @throws Throwable
     */
    public function handle(Tenant $tenant): void
    {
        $tenant->update(['provisioning_status' => 'provisioning']);

        try {
            $this->databaseProvisioner->provision($tenant);

            foreach ($this->domainsFor($tenant) as $domain) {
                $tenant->domains()->firstOrCreate(['domain' => $domain]);
            }

            $tenant->update(['provisioning_status' => 'active']);
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
