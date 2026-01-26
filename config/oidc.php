<?php

return [
    'issuer' => env('OIDC_ISSUER'),
    'client_id' => env('OIDC_CLIENT_ID'),
    'client_secret' => env('OIDC_CLIENT_SECRET'),
    'redirect_uri' => env('OIDC_REDIRECT_URI'),
    'scopes' => array_filter(array_map('trim', explode(' ', env('OIDC_SCOPES', 'openid profile email')))),
];
