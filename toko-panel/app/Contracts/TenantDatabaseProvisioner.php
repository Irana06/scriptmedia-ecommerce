<?php

namespace App\Contracts;

use App\Models\Tenant;

interface TenantDatabaseProvisioner
{
    public function provision(Tenant $tenant): void;
}
