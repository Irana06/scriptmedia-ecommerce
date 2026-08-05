<?php

use App\Models\Tenant;
use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Database\Models\Domain;
use Stancl\Tenancy\Database\Models\ImpersonationToken;
use Stancl\Tenancy\Database\TenantDatabaseManagers\MicrosoftSQLDatabaseManager;
use Stancl\Tenancy\Database\TenantDatabaseManagers\MySQLDatabaseManager;
use Stancl\Tenancy\Database\TenantDatabaseManagers\PostgreSQLDatabaseManager;
use Stancl\Tenancy\Database\TenantDatabaseManagers\SQLiteDatabaseManager;
use Stancl\Tenancy\Enums\RouteMode;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByOriginHeader;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Resolvers\DomainTenantResolver;

$appHost = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';
$extraCentralDomains = array_filter(array_map('trim', explode(',', (string) env('TENANCY_CENTRAL_DOMAINS', ''))));
$enginePath = (string) env('TOKO_ENGINE_PATH', base_path('../toko-engine'));

if (! preg_match('/^(?:[A-Za-z]:[\\\\\/]|\/)/', $enginePath)) {
    $enginePath = base_path($enginePath);
}

$engineMigrationsPath = rtrim($enginePath, '\\/').DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';

return [
    'models' => [
        'tenant' => Tenant::class,
        'domain' => Domain::class,
        'impersonation_token' => ImpersonationToken::class,
        'tenant_key_column' => 'tenant_id',
        'id_generator' => null,
    ],

    'identification' => [
        'central_domains' => array_values(array_unique([$appHost, ...$extraCentralDomains])),
        'default_middleware' => InitializeTenancyByDomain::class,
        'middleware' => [
            InitializeTenancyByDomain::class,
            InitializeTenancyBySubdomain::class,
            InitializeTenancyByDomainOrSubdomain::class,
            InitializeTenancyByPath::class,
            InitializeTenancyByRequestData::class,
            InitializeTenancyByOriginHeader::class,
        ],
        'domain_identification_middleware' => [
            InitializeTenancyByDomain::class,
            InitializeTenancyBySubdomain::class,
            InitializeTenancyByDomainOrSubdomain::class,
        ],
        'path_identification_middleware' => [
            InitializeTenancyByPath::class,
        ],
        'resolvers' => [
            DomainTenantResolver::class => [
                'cache' => env('TENANCY_DOMAIN_CACHE', false),
                'cache_store' => null,
                'cache_ttl' => 3600,
            ],
        ],
    ],

    'bootstrappers' => [
        DatabaseTenancyBootstrapper::class,
    ],

    'database' => [
        'central_connection' => env('DB_CONNECTION', 'sqlite'),
        'template_tenant_connection' => env('TENANCY_DB_CONNECTION'),
        'tenant_host_connection_name' => 'tenant_host_connection',
        'prefix' => '',
        'suffix' => '',
        'managers' => [
            'sqlite' => SQLiteDatabaseManager::class,
            'mysql' => MySQLDatabaseManager::class,
            'mariadb' => MySQLDatabaseManager::class,
            'pgsql' => PostgreSQLDatabaseManager::class,
            'sqlsrv' => MicrosoftSQLDatabaseManager::class,
        ],
        'drop_tenant_databases_on_migrate_fresh' => false,
    ],

    'base_domain' => strtolower((string) env('TENANCY_BASE_DOMAIN', 'scriptmedia.id')),
    'engine_path' => $enginePath,
    'routes' => false,
    'default_route_mode' => RouteMode::CENTRAL,
    'features' => [],
    'pending' => [
        'include_in_queries' => true,
        'count' => 0,
    ],
    'migration_parameters' => [
        '--force' => true,
        '--path' => [$engineMigrationsPath],
        '--realpath' => true,
    ],
    'seeder_parameters' => [
        '--class' => 'Database\\Seeders\\DatabaseSeeder',
    ],
];
