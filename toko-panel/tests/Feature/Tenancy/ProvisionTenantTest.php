<?php

namespace Tests\Feature\Tenancy;

use App\Actions\ProvisionTenant;
use App\Contracts\TenantDatabaseProvisioner;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Stancl\Tenancy\Database\TenantDatabaseManagers\SQLiteDatabaseManager;
use Tests\TestCase;

class ProvisionTenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_provisions_database_registers_domains_and_activates_tenant(): void
    {
        config(['tenancy.base_domain' => 'shops.test']);
        $tenant = Tenant::factory()->create([
            'subdomain' => 'kopi-kita',
            'custom_domain' => 'kopikita.id',
        ]);
        $provisioner = new class implements TenantDatabaseProvisioner
        {
            public bool $called = false;

            public function provision(Tenant $tenant): void
            {
                $this->called = true;
            }
        };

        (new ProvisionTenant($provisioner))->handle($tenant);

        $this->assertTrue($provisioner->called);
        $this->assertSame('active', $tenant->fresh()->provisioning_status);
        $this->assertDatabaseHas('domains', ['tenant_id' => $tenant->id, 'domain' => 'kopi-kita.shops.test']);
        $this->assertDatabaseHas('domains', ['tenant_id' => $tenant->id, 'domain' => 'kopikita.id']);
    }

    public function test_it_marks_tenant_failed_and_logs_detailed_context(): void
    {
        Log::spy();
        $tenant = Tenant::factory()->create(['subdomain' => 'gagal-provision']);
        $provisioner = new class implements TenantDatabaseProvisioner
        {
            public function provision(Tenant $tenant): void
            {
                throw new RuntimeException('Database host unavailable.');
            }
        };

        try {
            (new ProvisionTenant($provisioner))->handle($tenant);
            $this->fail('Provisioning exception was not rethrown.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Database host unavailable.', $exception->getMessage());
        }

        $this->assertSame('failed', $tenant->fresh()->provisioning_status);
        Log::shouldHaveReceived('error')
            ->once()
            ->with('Tenant provisioning failed.', \Mockery::on(
                fn (array $context): bool => $context['tenant_id'] === $tenant->id
                    && $context['database_name'] === $tenant->database_name
                    && $context['exception'] instanceof RuntimeException,
            ));
    }

    public function test_stancl_tenant_contract_uses_existing_database_name(): void
    {
        $tenant = Tenant::factory()->create(['database_name' => 'tenant_contract_check']);

        $this->assertSame($tenant->id, $tenant->getTenantKey());
        $this->assertSame('tenant_contract_check', $tenant->database()->getName());
    }

    public function test_real_stancl_provisioner_creates_the_toko_engine_schema(): void
    {
        $directory = storage_path('framework/testing/tenant-databases');
        $databaseName = 'provisioning_'.Str::lower(Str::random(10)).'.sqlite';
        $databasePath = $directory.DIRECTORY_SEPARATOR.$databaseName;

        File::ensureDirectoryExists($directory);
        SQLiteDatabaseManager::$path = $directory;
        config([
            'tenancy.base_domain' => 'shops.test',
            'tenancy.identification.central_domains' => ['toko-panel.test'],
            'tenancy.database.template_tenant_connection' => 'sqlite',
            'tenancy.migration_parameters.--path' => [base_path('../toko-engine/database/migrations')],
        ]);

        $tenant = Tenant::factory()->create([
            'subdomain' => 'schema-check',
            'database_name' => $databaseName,
        ]);

        try {
            app(ProvisionTenant::class)->handle($tenant);

            $tenant->run(function (): void {
                $this->assertTrue(Schema::hasTable('products'));
                $this->assertTrue(Schema::hasTable('orders'));
                $this->assertTrue(Schema::hasTable('store_settings'));
            });

            $this->get('http://schema-check.shops.test/_tenant/health')
                ->assertOk()
                ->assertJson([
                    'tenant_id' => $tenant->id,
                    'provisioning_status' => 'active',
                    'store_status' => 'active',
                ]);

            $this->assertSame('active', $tenant->fresh()->provisioning_status);
        } finally {
            tenancy()->end();
            DB::purge('tenant');
            SQLiteDatabaseManager::$path = null;
            File::delete($databasePath);
        }
    }
}
