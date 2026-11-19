<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReverbBroadcaster
{
    /**
     * Send an event using Reverb's Pusher-compatible HTTP API.
     * The event data is JSON encoded inside the outer Pusher request body.
     *
     * @param  array<int, string>  $channels
     * @param  array<string, mixed>  $data
     */
    public function broadcast(string $event, array $channels, array $data): void
    {
        $appId = (string) config('reverb.app_id');
        $key = (string) config('reverb.app_key');
        $secret = (string) config('reverb.app_secret');
        $host = (string) config('reverb.host');
        $port = (int) config('reverb.port', 8080);
        $scheme = (string) config('reverb.scheme', 'http');

        if ($appId === '' || $key === '' || $secret === '' || $host === '') {
            throw new RuntimeException('Reverb application credentials are not configured.');
        }

        $body = json_encode([
            'name' => $event,
            'channels' => $channels,
            'data' => json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $query = [
            'auth_key' => $key,
            'auth_timestamp' => (string) time(),
            'auth_version' => '1.0',
            'body_md5' => md5($body),
        ];
        ksort($query);

        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $path = '/apps/'.rawurlencode($appId).'/events';
        $signature = hash_hmac('sha256', "POST\n{$path}\n{$queryString}", $secret);
        $url = "{$scheme}://{$host}:{$port}{$path}?{$queryString}&auth_signature={$signature}";

        Http::timeout(3)
            ->withBody($body, 'application/json')
            ->post($url)
            ->throw();
    }
}
