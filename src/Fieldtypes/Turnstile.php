<?php

namespace JustBetter\StatamicTurnstile\Fieldtypes;

use JustBetter\StatamicTurnstile\Rules\ValidTurnstileToken;
use JustBetter\StatamicTurnstile\Turnstile as TurnstileService;
use Statamic\Fields\Fieldtype;

class Turnstile extends Fieldtype
{
    /** @var bool */
    protected $selectable = false;

    /** @var bool */
    protected $selectableInForms = true;

    /** @var array<int, string> */
    protected $categories = ['special'];

    /**
     * Cloudflare Turnstile logo (public domain, Wikimedia Commons).
     *
     * @var string
     */
    protected $icon = '<svg viewBox="0 0 54 54" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M27.315 7.261a19.45 19.45 0 0 0-13.518 4.917l1.23-6.743-3.193-.582-2.162 11.836 11.84 2.16.582-3.193-6.08-1.11a16.173 16.173 0 1 1-4.982 8.064l-3.142-.824A19.478 19.478 0 1 0 27.315 7.261z" fill="#F38020"/><path fill-rule="evenodd" clip-rule="evenodd" d="M38.847 21.919 35.928 19 24.477 30.452 19.923 25.9 17 28.822l7.483 7.484 2.923-2.923-.011-.012L38.847 21.92z" fill="#F38020"/></svg>';

    /** @var string */
    protected $component = 'hidden';

    /** @return array<int, mixed> */
    public function rules(): array
    {
        if (! app(TurnstileService::class)->isActive()) {
            return [];
        }

        // An explicit "required" prevents Statamic from adding "nullable", which would skip the token rule.
        return ['required', new ValidTurnstileToken];
    }

    public function process(mixed $data): mixed
    {
        return null;
    }

    public function view(): string
    {
        return 'statamic-turnstile::field';
    }

    /** @return array<string, array<string, mixed>> */
    protected function configFieldItems(): array
    {
        return [
            'theme' => [
                'display' => __('Theme'),
                'type' => 'select',
                'default' => 'auto',
                'options' => [
                    'auto' => __('Auto'),
                    'light' => __('Light'),
                    'dark' => __('Dark'),
                ],
            ],
            'size' => [
                'display' => __('Size'),
                'type' => 'select',
                'default' => 'flexible',
                'options' => [
                    'normal' => __('Normal'),
                    'flexible' => __('Flexible'),
                    'compact' => __('Compact'),
                ],
            ],
            'appearance' => [
                'display' => __('Appearance'),
                'instructions' => __('When the widget is shown to the visitor.'),
                'type' => 'select',
                'default' => 'always',
                'options' => [
                    'always' => __('Always'),
                    'interaction-only' => __('Only when interaction is required'),
                ],
            ],
        ];
    }
}
