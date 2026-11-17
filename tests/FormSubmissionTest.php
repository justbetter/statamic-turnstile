<?php

namespace JustBetter\StatamicTurnstile\Tests;

use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form;
use Symfony\Component\HttpFoundation\Response;

class FormSubmissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $blueprint = Blueprint::makeFromFields([
            'name' => ['type' => 'text'],
            'turnstile' => ['type' => 'turnstile'],
        ]);

        Blueprint::shouldReceive('find')->with('forms.contact')->andReturn($blueprint);

        $form = Form::make('contact');
        $form->title('Contact');
        $form->save();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TestResponse<Response>
     */
    protected function submit(array $data): TestResponse
    {
        return $this->post('/!/forms/contact', $data, ['X-Requested-With' => 'XMLHttpRequest']);
    }

    #[Test]
    public function it_accepts_a_submission_with_a_valid_token_and_does_not_store_it(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'hostname' => 'localhost'])]);

        $this->submit(['name' => 'Kevin', 'turnstile' => 'token'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('submission.name', 'Kevin')
            ->assertJsonPath('submission.turnstile', null);
    }

    #[Test]
    public function it_rejects_a_submission_without_a_token(): void
    {
        Http::fake();

        $this->submit(['name' => 'Kevin'])
            ->assertStatus(400)
            ->assertJsonStructure(['error' => ['turnstile']]);

        Http::assertNothingSent();
    }

    #[Test]
    public function it_rejects_a_submission_with_an_invalid_token(): void
    {
        Http::fake(['*' => Http::response(['success' => false])]);

        $this->submit(['name' => 'Kevin', 'turnstile' => 'token'])
            ->assertStatus(400)
            ->assertJsonPath('error.turnstile', 'We could not verify that you are human. Please try again.');
    }

    #[Test]
    public function it_accepts_a_submission_without_a_token_when_inactive(): void
    {
        $this->disableTurnstile();

        Http::fake();

        $this->submit(['name' => 'Kevin'])
            ->assertOk()
            ->assertJsonPath('success', true);

        Http::assertNothingSent();
    }
}
