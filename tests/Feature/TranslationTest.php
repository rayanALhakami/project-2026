<?php

namespace Tests\Feature;

use App\Ai\Agents\Translator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_translate_text_into_a_supported_language(): void
    {
        Translator::fake(['Where is the nearest restaurant?']);

        $response = $this->postJson(route('translate.store'), [
            'text' => 'أين أقرب مطعم؟',
            'target_language' => 'en',
        ]);

        $response->assertOk()
            ->assertExactJson([
                'translation' => 'Where is the nearest restaurant?',
                'target_language' => 'en',
                'source_language' => null,
            ]);

        Translator::assertPrompted('أين أقرب مطعم؟');
    }

    public function test_the_text_is_required_before_prompting_the_agent(): void
    {
        Translator::fake();

        $this->postJson(route('translate.store'), ['target_language' => 'en'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('text');

        Translator::assertNeverPrompted();
    }

    public function test_the_target_language_must_be_supported_before_prompting_the_agent(): void
    {
        Translator::fake();

        $this->postJson(route('translate.store'), [
            'text' => 'أين أقرب مطعم؟',
            'target_language' => 'xx',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('target_language');

        Translator::assertNeverPrompted();
    }

    public function test_translation_failures_return_a_generic_arabic_error(): void
    {
        Translator::fake(fn () => throw new RuntimeException('Provider exploded with secret detail.'));

        $response = $this->postJson(route('translate.store'), [
            'text' => 'أين أقرب مطعم؟',
            'target_language' => 'en',
        ]);

        $response->assertStatus(503)
            ->assertJsonStructure(['error'])
            ->assertJsonMissingPaths(['exception', 'trace', 'file', 'line']);

        $this->assertMatchesRegularExpression('/\p{Arabic}/u', $response->json('error'));
        $this->assertStringNotContainsString('Provider exploded', $response->getContent());
        $this->assertStringNotContainsString('RuntimeException', $response->getContent());
    }
}
