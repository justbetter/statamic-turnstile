<?php

namespace JustBetter\StatamicTurnstile\Tags;

use Illuminate\Support\Facades\Blade;
use JustBetter\StatamicTurnstile\Turnstile as TurnstileService;
use JustBetter\StatamicTurnstile\View\Components\Widget;
use Statamic\Tags\Tags;

class Turnstile extends Tags
{
    public function index(): string
    {
        return $this->widget();
    }

    public function widget(): string
    {
        // Blade::renderComponent() does not call shouldRender(), so check the gate here.
        if (! app(TurnstileService::class)->isActive()) {
            return '';
        }

        return Blade::renderComponent(new Widget(
            handle: $this->stringParam('handle') ?? 'turnstile',
            frontend: $this->stringParam('frontend'),
            theme: $this->stringParam('theme'),
            size: $this->stringParam('size'),
            appearance: $this->stringParam('appearance'),
        ));
    }

    protected function stringParam(string $key): ?string
    {
        $value = $this->params->get($key);

        return is_string($value) ? $value : null;
    }
}
