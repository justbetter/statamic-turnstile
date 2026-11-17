<?php

namespace JustBetter\StatamicTurnstile;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Turnstile
{
    public const array FRONTENDS = ['alpine', 'vue', 'vanilla'];

    public function isActive(): bool
    {
        return config('justbetter.turnstile.enabled') == true
            && $this->siteKey() !== ''
            && $this->secretKey() !== '';
    }

    public function siteKey(): string
    {
        return $this->stringConfig('justbetter.turnstile.site_key');
    }

    public function frontend(?string $frontend = null): string
    {
        $frontend ??= $this->stringConfig('justbetter.turnstile.frontend');

        return in_array($frontend, self::FRONTENDS, true) ? $frontend : 'alpine';
    }

    public function verify(?string $token, ?string $ip = null, ?string $hostname = null): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout($this->intConfig('justbetter.turnstile.timeout', 5))
                ->post($this->stringConfig('justbetter.turnstile.verify_url'), array_filter([
                    'secret' => $this->secretKey(),
                    'response' => $token,
                    'remoteip' => $ip,
                ]));
        } catch (ConnectionException $exception) {
            return $this->unreachable($exception->getMessage());
        }

        if (! $response->successful()) {
            return $this->unreachable('HTTP '.$response->status());
        }

        if ($response->json('success') !== true) {
            Log::info('Turnstile verification failed.', [
                'error-codes' => $response->json('error-codes', []),
            ]);

            return false;
        }

        $verifiedHostname = $response->json('hostname');

        if (config('justbetter.turnstile.check_hostname') == true && $hostname !== null && is_string($verifiedHostname) && $verifiedHostname !== $hostname) {
            Log::info('Turnstile hostname mismatch.', [
                'expected' => $hostname,
                'actual' => $verifiedHostname,
            ]);

            return false;
        }

        return true;
    }

    protected function secretKey(): string
    {
        return $this->stringConfig('justbetter.turnstile.secret_key');
    }

    protected function unreachable(string $reason): bool
    {
        $failOpen = config('justbetter.turnstile.fail_open') == true;

        Log::warning('Turnstile verification could not be completed.', [
            'reason' => $reason,
            'fail_open' => $failOpen,
        ]);

        return $failOpen;
    }

    protected function stringConfig(string $key): string
    {
        $value = config($key);

        return is_string($value) ? $value : '';
    }

    protected function intConfig(string $key, int $default): int
    {
        $value = config($key);

        return is_numeric($value) ? (int) $value : $default;
    }
}
