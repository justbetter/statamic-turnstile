<?php

namespace JustBetter\StatamicTurnstile\Tests;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use JustBetter\StatamicTurnstile\Turnstile;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class TurnstileTest extends TestCase
{
    protected function turnstile(): Turnstile
    {
        return app(Turnstile::class);
    }

    #[Test]
    public function it_is_active_when_enabled_and_configured(): void
    {
        $this->assertTrue($this->turnstile()->isActive());
        $this->assertSame('test-site-key', $this->turnstile()->siteKey());
    }

    /** @return array<string, array{string, mixed}> */
    public static function inactiveConfig(): array
    {
        return [
            'disabled' => ['justbetter.turnstile.enabled', false],
            'missing site key' => ['justbetter.turnstile.site_key', null],
            'missing secret key' => ['justbetter.turnstile.secret_key', ''],
        ];
    }

    #[Test]
    #[DataProvider('inactiveConfig')]
    public function it_is_inactive_when_not_enabled_or_configured(string $key, mixed $value): void
    {
        config()->set($key, $value);

        $this->assertFalse($this->turnstile()->isActive());
    }

    #[Test]
    public function it_resolves_the_frontend(): void
    {
        $this->assertSame('alpine', $this->turnstile()->frontend());
        $this->assertSame('vue', $this->turnstile()->frontend('vue'));
        $this->assertSame('alpine', $this->turnstile()->frontend('react'));

        config()->set('justbetter.turnstile.frontend', 'vanilla');

        $this->assertSame('vanilla', $this->turnstile()->frontend());
    }

    #[Test]
    public function it_rejects_a_missing_token_without_calling_cloudflare(): void
    {
        Http::fake();

        $this->assertFalse($this->turnstile()->verify(null));
        $this->assertFalse($this->turnstile()->verify(''));

        Http::assertNothingSent();
    }

    #[Test]
    public function it_verifies_a_valid_token(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'hostname' => 'example.com'])]);

        $this->assertTrue($this->turnstile()->verify('token', '127.0.0.1', 'example.com'));

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && $request['secret'] === 'test-secret-key'
            && $request['response'] === 'token'
            && $request['remoteip'] === '127.0.0.1');
    }

    #[Test]
    public function it_rejects_a_token_cloudflare_does_not_accept(): void
    {
        Http::fake(['*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]);

        $this->assertFalse($this->turnstile()->verify('token'));
    }

    #[Test]
    public function it_rejects_a_token_issued_for_another_hostname(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'hostname' => 'other.com'])]);

        $this->assertFalse($this->turnstile()->verify('token', null, 'example.com'));
    }

    #[Test]
    public function it_skips_the_hostname_check_when_disabled(): void
    {
        config()->set('justbetter.turnstile.check_hostname', false);

        Http::fake(['*' => Http::response(['success' => true, 'hostname' => 'other.com'])]);

        $this->assertTrue($this->turnstile()->verify('token', null, 'example.com'));
    }

    #[Test]
    public function it_fails_closed_when_cloudflare_is_unreachable(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->assertFalse($this->turnstile()->verify('token'));
    }

    #[Test]
    public function it_fails_open_when_configured_and_cloudflare_errors(): void
    {
        config()->set('justbetter.turnstile.fail_open', true);
        config()->set('justbetter.turnstile.timeout', 'invalid');

        Http::fake(['*' => Http::response('', 500)]);

        $this->assertTrue($this->turnstile()->verify('token'));
    }
}
