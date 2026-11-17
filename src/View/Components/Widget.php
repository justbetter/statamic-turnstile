<?php

namespace JustBetter\StatamicTurnstile\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use JustBetter\StatamicTurnstile\Turnstile;

class Widget extends Component
{
    public function __construct(
        public string $handle = 'turnstile',
        public ?string $frontend = null,
        public ?string $theme = null,
        public ?string $size = null,
        public ?string $appearance = null,
    ) {}

    public function shouldRender(): bool
    {
        return app(Turnstile::class)->isActive();
    }

    public function render(): View
    {
        $turnstile = app(Turnstile::class);

        return $this->view('statamic-turnstile::widget', [
            'frontend' => $turnstile->frontend($this->frontend),
            'options' => array_filter([
                'sitekey' => $turnstile->siteKey(),
                'responseFieldName' => $this->handle,
                'theme' => $this->theme,
                'size' => $this->size,
                'appearance' => $this->appearance,
            ]),
        ]);
    }
}
