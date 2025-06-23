<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\CanvaToken;

if (! function_exists('canvaAccessToken')) {
    function canvaAccessToken(): ?string
    {
        $row = CanvaToken::first();
        if (! $row) return null;

        if (now()->lt($row->expires_at)) {
            return $row->access_token;
        }

        // Refresh the token
        $res = Http::asForm()
            ->withBasicAuth(config('services.canva.client_id'), config('services.canva.client_secret'))
            ->post('https://api.canva.com/rest/v1/oauth/token', [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $row->refresh_token,
            ]);

        if ($res->failed()) {
            Log::error('Could not refresh Canva token: '.$res->body());
            return null;
        }

        $json = $res->json();

        $row->update([
            'access_token'  => $json['access_token'],
            'refresh_token' => $json['refresh_token'] ?? $row->refresh_token,
            'expires_at'    => now()->addSeconds($json['expires_in'] - 60),
        ]);

        return $json['access_token'];
    }
}
