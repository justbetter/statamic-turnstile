<?php

namespace JustBetter\StatamicTurnstile\Tests;

use JustBetter\StatamicTurnstile\StatamicTurnstileServiceProvider;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = StatamicTurnstileServiceProvider::class;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:7tG0yY7g3QkFrQ+Vk4EBSbcT8D9C4/5Dph1dNRjh6WU=');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('justbetter.turnstile.enabled', true);
        $app['config']->set('justbetter.turnstile.site_key', 'test-site-key');
        $app['config']->set('justbetter.turnstile.secret_key', 'test-secret-key');
    }

    protected function disableTurnstile(): void
    {
        config()->set('justbetter.turnstile.enabled', false);
    }
}
