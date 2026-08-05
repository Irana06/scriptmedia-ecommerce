<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleBasedLoginResponse implements LoginResponse, PasskeyLoginResponse, TwoFactorLoginResponse
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, Response::HTTP_UNAUTHORIZED);

        $route = match (true) {
            $user->hasRole(UserRole::Admin->value) => 'admin.dashboard',
            $user->hasRole(UserRole::Owner->value) => 'portal.dashboard',
            default => abort(Response::HTTP_FORBIDDEN, 'Akun belum memiliki role aplikasi.'),
        };

        $redirectUrl = route($route);

        if ($request->wantsJson()) {
            return new JsonResponse([
                'redirect' => $redirectUrl,
                'two_factor' => false,
            ]);
        }

        return redirect()->to($redirectUrl);
    }
}
