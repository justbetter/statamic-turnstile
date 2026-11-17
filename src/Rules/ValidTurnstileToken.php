<?php

namespace JustBetter\StatamicTurnstile\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use JustBetter\StatamicTurnstile\Turnstile;

class ValidTurnstileToken implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $request = request();

        $isValid = app(Turnstile::class)->verify(
            is_string($value) ? $value : null,
            $request->ip(),
            $request->getHost(),
        );

        if (! $isValid) {
            $fail('statamic-turnstile::messages.failed')->translate();
        }
    }
}
