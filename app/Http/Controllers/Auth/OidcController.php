<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OidcController extends Controller
{
    /**
     * Redirect ke Keycloak
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('keycloak')->redirect();
    }

    /**
     * Callback dari Keycloak
     */
    public function callback(Request $request): Response
    {
        // kalau akses manual tanpa code
        if (! $request->has('code') && ! $request->has('state')) {
            return response('OIDC callback OK (no authorization code).', 200);
        }

        try {
            // ambil user dari Keycloak
            $kcUser = Socialite::driver('keycloak')->user();

            // ambil access token
            $accessToken = $kcUser->token;

            // decode JWT access token
            $payload = json_decode(
                base64_decode(explode('.', $accessToken)[1]),
                true
            );

            Log::info('KEYCLOAK TOKEN PAYLOAD', $payload);

        } catch (Throwable $e) {
            Log::error('SSO ERROR', ['error' => $e->getMessage()]);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'SSO login failed.']);
        }

        /**
         * =============================
         * AMBIL DATA DARI TOKEN
         * =============================
         */
        $email = $kcUser->getEmail();
        if (! $email) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Email not provided by SSO.']);
        }

        $name = $kcUser->getName()
            ?: ($payload['preferred_username'] ?? $email);

        $roles = $payload['roles'] ?? [];


        $nim = $payload['nim'] ?? null;

        /**
         * =============================
         * MAPPING ROLE (FINAL)
         * =============================
         */
        $role =
            in_array('admin', $roles) ? 'admin' :
            (in_array('dosen', $roles) ? 'dosen' : 'mahasiswa');

        /**
         * =============================
         * SIMPAN / UPDATE USER
         * =============================
         */
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'role'              => $role,
                'nim'               => $nim, 
                'password'          => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
            ]
        );

        /**
         * =============================
         * LOGIN USER
         * =============================
         */
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect('/dashboard');
}

    /**
     * Logout Laravel + Keycloak
     */
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $keycloakLogoutUrl =
            'http://localhost:8080/realms/unsika/protocol/openid-connect/logout'
            .'?client_id=laravel-app'
            .'&post_logout_redirect_uri='.urlencode('http://sso.local');

        return redirect($keycloakLogoutUrl);
    }
}
