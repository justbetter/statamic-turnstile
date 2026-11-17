<?php

namespace JustBetter\StatamicTurnstile\Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use JustBetter\StatamicTurnstile\Fieldtypes\Turnstile;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Antlers;
use Statamic\Fields\Field;

class RenderingTest extends TestCase
{
    #[Test]
    public function it_renders_the_alpine_widget_by_default(): void
    {
        $html = Blade::render('<x-turnstile::widget handle="captcha" theme="dark" />');

        $this->assertStringContainsString('x-turnstile=', $html);
        $this->assertStringContainsString('test-site-key', $html);
        $this->assertStringContainsString('captcha', $html);
        $this->assertStringContainsString('dark', $html);
    }

    #[Test]
    public function it_renders_the_vue_widget(): void
    {
        $html = Blade::render('<x-turnstile::widget frontend="vue" size="compact" />');

        $this->assertStringContainsString('<turnstile-widget', $html);
        $this->assertStringContainsString('sitekey="test-site-key"', $html);
        $this->assertStringContainsString('response-field-name="turnstile"', $html);
        $this->assertStringContainsString('size="compact"', $html);
    }

    #[Test]
    public function it_renders_the_vanilla_widget(): void
    {
        $html = Blade::render('<x-turnstile::widget frontend="vanilla" />');

        $this->assertStringContainsString('data-jb-turnstile', $html);
        $this->assertStringContainsString('data-sitekey="test-site-key"', $html);
        $this->assertStringContainsString('data-response-field-name="turnstile"', $html);
    }

    #[Test]
    public function it_renders_nothing_when_inactive(): void
    {
        $this->disableTurnstile();

        $this->assertSame('', trim(Blade::render('<x-turnstile::widget />')));
        $this->assertSame('', trim((string) Antlers::parse('{{ turnstile }}', [], true)));
    }

    #[Test]
    public function it_renders_the_antlers_tag(): void
    {
        $html = (string) Antlers::parse('{{ turnstile }}{{ turnstile:widget handle="captcha" frontend="vanilla" }}', [], true);

        $this->assertStringContainsString('x-turnstile=', $html);
        $this->assertStringContainsString('data-response-field-name="captcha"', $html);
    }

    #[Test]
    public function it_renders_the_field_view(): void
    {
        $field = new Field('captcha', ['type' => 'turnstile', 'appearance' => 'interaction-only']);

        $html = View::make($field->fieldtype()->view(), [
            'handle' => 'captcha',
            'appearance' => 'interaction-only',
        ])->render();

        $this->assertStringContainsString('captcha', $html);
        $this->assertStringContainsString('interaction-only', $html);
    }

    #[Test]
    public function it_exposes_the_fieldtype_config(): void
    {
        $fieldtype = new Turnstile;

        $this->assertSame(['theme', 'size', 'appearance'], $fieldtype->configFields()->all()->keys()->all());
        $this->assertTrue($fieldtype->selectableInForms());
        $this->assertFalse($fieldtype->selectable());
        $this->assertSame('hidden', $fieldtype->component());
        $this->assertNull($fieldtype->process('token'));
    }
}
