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
        return Socialite::driver('keycloak')
        ->with([
            'prompt' => 'login',
        ])
        ->redirect();
    }

    /**
     * Callback dari Keycloak
     */
    public function callback(Request $request): Response
    {   

        if (! $request->has('code')) {
            return response('OIDC callback OK', 200);
        }

        try {
            $kcUser = Socialite::driver('keycloak')->user();
            $accessToken = $kcUser->token;

            $payload = json_decode(
                base64_decode(explode('.', $accessToken)[1]),
                true
            );

            Log::info('KEYCLOAK TOKEN PAYLOAD', $payload);

        } catch (\Throwable $e) {
            Log::error('SSO ERROR', ['error' => $e->getMessage()]);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'SSO login failed.']);
        }

        // =============================
        // AMBIL DATA TOKEN
        // =============================
        $email = $kcUser->getEmail();
        if (! $email) {
            abort(403, 'Email tidak tersedia dari SSO');
        }

        $name = $kcUser->getName()
            ?: ($payload['preferred_username'] ?? $email);

        // ambil roles dari token
        $roles = $payload['realm_access']['roles'] ?? [];

        // mapping role aplikasi
        if (in_array('admin', $roles)) {
            $role = 'admin';
        } elseif (in_array('dosen', $roles)) {
            $role = 'dosen';
        } elseif (in_array('mahasiswa', $roles)) {
            $role = 'mahasiswa';
        } else {
            abort(403, 'Role tidak dikenali');
        }

        // ambil atribut kampus
        $nim = $payload['nim'] ?? null;
        $nip = $payload['nip'] ?? null;

        // =============================
        // MAPPING ROLE (AMAN)
        // =============================
        if (in_array('admin', $roles)) {
            $role = 'admin';
        } elseif (in_array('dosen', $roles)) {
            $role = 'dosen';
        } elseif (in_array('mahasiswa', $roles)) {
            $role = 'mahasiswa';
        } else {
            abort(403, 'Role tidak dikenali');
        }

        // =============================
        // SIMPAN USER
        // =============================
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'role'              => $role,
                'nim'               => $nim,
                'nip'               => $nip,
                'email_verified_at' => now(),
            ]
        );

        Auth::login($user);
        $request->session()->regenerate();

        // =============================
        // AUTO REDIRECT SESUAI ROLE
        // =============================
        return redirect(match ($role) {
            'admin' => '/admin/dashboard',
            'dosen' => '/dosen/dashboard',
            'mahasiswa' => '/mahasiswa/dashboard',
        });
    }


    public function logout(Request $request)
    {
        $idToken = session('id_token');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $keycloakLogoutUrl = config('services.keycloak.base_url')
            . '/realms/' . config('services.keycloak.realms')
            . '/protocol/openid-connect/logout';

        $params = [
            'client_id' => config('services.keycloak.client_id'),
            'post_logout_redirect_uri' => route('login'),
        ];

        if ($idToken) {
            $params['id_token_hint'] = $idToken;
        }

        return redirect($keycloakLogoutUrl . '?' . http_build_query($params));
    }

}
