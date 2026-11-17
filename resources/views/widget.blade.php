@switch($frontend)
    @case('vue')
        <turnstile-widget
            @foreach ($options as $option => $value)
                {{ Str::kebab($option) }}="{{ $value }}"
            @endforeach
        ></turnstile-widget>
        @break

    @case('vanilla')
        <div
            data-jb-turnstile
            @foreach ($options as $option => $value)
                data-{{ Str::kebab($option) }}="{{ $value }}"
            @endforeach
        ></div>
        @break

    @default
        <div x-data x-turnstile="{{ Js::from($options) }}"></div>
@endswitch
