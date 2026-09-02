<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        if ($user->hasRole(UserRole::Admin->value)) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole(UserRole::Owner->value)) {
            $selectedPlan = $request->session()->pull('selected_plan');

            if (is_string($selectedPlan) && $selectedPlan !== '') {
                return redirect()->route('onboarding.create', $selectedPlan);
            }

            return redirect()->route('portal.dashboard');
        }

        abort(Response::HTTP_FORBIDDEN, 'Akun belum memiliki role aplikasi.');
    }
}
