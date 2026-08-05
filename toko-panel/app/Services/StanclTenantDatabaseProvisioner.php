<?php

namespace App\Services;

use App\Contracts\TenantDatabaseProvisioner;
use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use Stancl\Tenancy\Database\DatabaseManager;

class StanclTenantDatabaseProvisioner implements TenantDatabaseProvisioner
{
    public function __construct(private readonly DatabaseManager $databaseManager) {}

    public function provision(Tenant $tenant): void
    {
        $this->ensureEngineMigrationsExist();

        $database = $tenant->database();
        $database->makeCredentials();
        $manager = $database->manager();

        if (! $manager->databaseExists($database->getName())) {
            $this->databaseManager->ensureTenantCanBeCreated($tenant);

            if (! $manager->createDatabase($tenant)) {
                throw new RuntimeException("Database tenant [{$database->getName()}] gagal dibuat.");
            }
        }

        $exitCode = Artisan::call('tenants:migrate', [
            '--tenants' => [(string) $tenant->getTenantKey()],
            '--with-pending' => true,
        ]);

        if ($exitCode !== 0) {
            throw new RuntimeException('Migrasi database tenant gagal: '.trim(Artisan::output()));
        }
    }

    private function ensureEngineMigrationsExist(): void
    {
        $paths = config('tenancy.migration_parameters.--path', []);
        $path = is_array($paths) ? ($paths[0] ?? null) : $paths;

        if (! is_string($path) || ! is_dir($path)) {
            throw new RuntimeException('Path migrasi toko-engine tidak ditemukan. Periksa TOKO_ENGINE_PATH.');
        }

        if (glob(rtrim($path, '\\/').DIRECTORY_SEPARATOR.'*.php') === []) {
            throw new RuntimeException("Tidak ada migration PHP di path toko-engine [{$path}].");
        }
    }
}
