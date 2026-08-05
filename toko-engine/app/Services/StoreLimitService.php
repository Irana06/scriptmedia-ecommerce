<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\Product;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class StoreLimitService
{
    /** @var array{max_products: int|null, max_payment_gateways: int|null}|null */
    private ?array $limits = null;

    public function canAddProduct(): bool
    {
        $limit = $this->productLimit();

        return $limit === null || Product::query()->count() < $limit;
    }

    public function canUseGateway(): bool
    {
        $limit = $this->gatewayLimit();

        return $limit === null || PaymentGateway::query()->where('is_active', true)->count() < $limit;
    }

    public function productLimit(): ?int
    {
        return $this->resolveLimits()['max_products'];
    }

    public function gatewayLimit(): ?int
    {
        return $this->resolveLimits()['max_payment_gateways'];
    }

    /** @return array{max_products: int|null, max_payment_gateways: int|null} */
    private function resolveLimits(): array
    {
        if ($this->limits !== null) {
            return $this->limits;
        }

        $fallback = $this->fallbackLimits();

        if (blank(config('database.connections.central.database'))) {
            return $this->limits = $fallback;
        }

        try {
            $tenantDatabase = config('store-limits.tenant_database');

            if (blank($tenantDatabase)) {
                $tenantDatabase = DB::connection()->getDatabaseName();
            }

            $plan = $this->centralConnection()
                ->table('tenants')
                ->join('subscriptions', 'subscriptions.tenant_id', '=', 'tenants.id')
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->where('tenants.database_name', (string) $tenantDatabase)
                ->whereIn('tenants.store_status', ['active', 'grace_period'])
                ->whereIn('subscriptions.status', ['active', 'grace_period'])
                ->latest('subscriptions.id')
                ->select(['plans.max_products', 'plans.max_payment_gateways'])
                ->first();

            if ($plan === null) {
                return $this->limits = $fallback;
            }

            return $this->limits = [
                'max_products' => $this->normalizeLimit($plan->max_products),
                'max_payment_gateways' => $this->normalizeLimit($plan->max_payment_gateways),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return $this->limits = $fallback;
        }
    }

    /** @return array{max_products: int|null, max_payment_gateways: int|null} */
    private function fallbackLimits(): array
    {
        return [
            'max_products' => $this->normalizeLimit(config('store-limits.fallback.max_products')),
            'max_payment_gateways' => $this->normalizeLimit(config('store-limits.fallback.max_payment_gateways')),
        ];
    }

    private function normalizeLimit(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value >= 0 ? (int) $value : null;
    }

    private function centralConnection(): ConnectionInterface
    {
        return DB::connection('central');
    }
}
