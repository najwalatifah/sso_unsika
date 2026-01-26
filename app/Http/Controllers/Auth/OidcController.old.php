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
    public function redirect(): RedirectResponse
    {
        dd(config('services.keycloak'));

        return Socialite::driver('keycloak')->redirect();
    }

    public function callback(Request $request): Response
    {
        $token = $oidc->getAccessTokenPayload();
        dd($token);

        // TEST MANUAL (tanpa Keycloak)
        if (! $request->has('code') && ! $request->has('state')) {
            return response('OIDC callback OK (no authorization code).', 200);
        }

        try {
            $kcUser = Socialite::driver('keycloak')->user();
            $accessToken = $kcUser->token;
            // decode JWT access token
            $payload = json_decode(
                base64_decode(explode('.', $accessToken)[1]),
                true
            );

            Log::info('KEYCLOAK TOKEN PAYLOAD', $payload);

        } catch (Throwable $e) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'SSO login failed.']);
        }

        $email = $kcUser->getEmail();
        if (! $email) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Email not provided by SSO.']);
        }

        $name = $kcUser->getName() ?: $email;

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => bcrypt(Str::random(32)),
                'email_verified_at' => now(),
            ]
        );

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect('/dashboard');

    }

    public function logout()
    {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $keycloakLogoutUrl =
            'http://localhost:8080/realms/unsika/protocol/openid-connect/logout'
            .'?client_id=laravel-app'
            .'&post_logout_redirect_uri='.urlencode('http://sso.local');

        return redirect($keycloakLogoutUrl);
    }
}
