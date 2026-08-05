<?php

namespace App\Actions;

use App\Models\ContentChangeRequest;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateContentChangeRequest
{
    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(Tenant $tenant, User $requester, string $description): ContentChangeRequest
    {
        if ($tenant->owner_user_id !== $requester->getKey()) {
            throw new AuthorizationException('Anda tidak dapat mengajukan tiket untuk tenant ini.');
        }

        return DB::transaction(function () use ($tenant, $requester, $description): ContentChangeRequest {
            $subscription = Subscription::query()
                ->with('plan')
                ->where('tenant_id', $tenant->getKey())
                ->where('status', 'active')
                ->whereDate('current_period_start', '<=', today())
                ->whereDate('current_period_end', '>=', today())
                ->latest('current_period_start')
                ->lockForUpdate()
                ->first();

            if (! $subscription instanceof Subscription) {
                throw ValidationException::withMessages([
                    'description' => 'Tenant belum memiliki subscription aktif pada periode ini.',
                ]);
            }

            $periodStart = $subscription->current_period_start->copy();
            $periodEnd = $subscription->current_period_end->copy();
            $quota = $subscription->plan->content_request_quota;
            $used = $tenant->contentChangeRequests()
                ->whereDate('usage_period_start', '>=', $periodStart)
                ->whereDate('usage_period_start', '<=', $periodEnd)
                ->count();

            $usage = $tenant->planFeatureUsages()->firstOrCreate(
                ['period_start' => $subscription->current_period_start],
                ['products_count' => 0, 'content_requests_used' => 0],
            );
            $usage->content_requests_used = $used;
            $usage->save();

            if ($used >= $quota) {
                throw ValidationException::withMessages([
                    'description' => "Kuota request konten periode ini sudah habis ({$used}/{$quota}). Silakan upgrade plan untuk menambah kuota.",
                ]);
            }

            $contentRequest = $tenant->contentChangeRequests()->create([
                'requested_by_user_id' => $requester->getKey(),
                'description' => $description,
                'status' => 'pending',
                'usage_period_start' => $subscription->current_period_start,
            ]);

            $usage->increment('content_requests_used');

            return $contentRequest;
        });
    }
}
