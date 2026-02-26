<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class OidcController extends Controller
{
    /**
     * Redirect ke Keycloak
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('keycloak')
            ->with([
                'prompt' => 'login',
            ])
            ->redirect();
    }

    /**
     * Callback dari Keycloak
     */
    public function callback(Request $request)
    {
        try {
            $kcUser = Socialite::driver('keycloak')->user();

            $raw = $kcUser->accessTokenResponseBody;
            $idToken = $raw['id_token'] ?? null;

            if ($idToken) {
                session(['id_token' => $idToken]);
            }

            $accessToken = $kcUser->token;
            $payload = json_decode(
                base64_decode(explode('.', $accessToken)[1]),
                true
            );

            $email = $kcUser->getEmail()
                ?? ($payload['email'] ?? null)
                ?? ($payload['preferred_username'] ?? null);

            if (! $email) {
                throw new \Exception('Email tidak tersedia');
            }

            $nim = $payload['nim'] ?? null;
            $nip = $payload['nip'] ?? null;

            // =============================
            // AUTO ROLE
            // =============================
            if ($nip) {
                $role = 'dosen';
            } elseif ($nim) {
                $role = 'mahasiswa';
            } else {
                throw new \Exception('Akun tidak memiliki NIM atau NIP');
            }

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $kcUser->getName() ?? $email,
                    'role' => $role,
                    'nim' => $nim,
                    'nip' => $nip,
                ]
            );

            Auth::login($user);
            $request->session()->regenerate();

            return redirect("/{$role}/dashboard");

        } catch (\Throwable $e) {
            Log::error('SSO SYNC ERROR', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'SSO login failed']);
        }
    }

    public function logout(Request $request)
    {
        $idToken = session('id_token');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $keycloakLogoutUrl = config('services.keycloak.base_url')
            .'/realms/'.config('services.keycloak.realms')
            .'/protocol/openid-connect/logout';

        $params = [
            'client_id' => config('services.keycloak.client_id'),
            'post_logout_redirect_uri' => route('login'),
        ];

        if ($idToken) {
            $params['id_token_hint'] = $idToken;
        }

        return redirect($keycloakLogoutUrl.'?'.http_build_query($params));
    }
}
