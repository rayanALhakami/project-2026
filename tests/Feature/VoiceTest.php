<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Ai\Audio;
use Laravel\Ai\Prompts\AudioPrompt;
use Laravel\Ai\Prompts\TranscriptionPrompt;
use Laravel\Ai\Transcription;
use RuntimeException;
use Tests\TestCase;

class VoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_transcribe_an_audio_recording(): void
    {
        Transcription::fake(['مرحبا بكم']);

        $response = $this->post(
            route('assistant.transcribe'),
            [
                'audio' => UploadedFile::fake()->createWithContent('voice.webm', 'fake-webm-bytes'),
                'language' => 'ar',
            ],
            ['Accept' => 'application/json'],
        );

        $response->assertOk()
            ->assertExactJson(['text' => 'مرحبا بكم']);

        Transcription::assertGenerated(
            fn (TranscriptionPrompt $prompt): bool => $prompt->language === 'ar'
                && $prompt->audio->mimeType() === 'video/webm'
                && $prompt->audio->content() === 'fake-webm-bytes',
        );
    }

    public function test_the_audio_file_is_required_before_transcribing(): void
    {
        Transcription::fake();

        $this->postJson(route('assistant.transcribe'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('audio');

        Transcription::assertNothingGenerated();
    }

    public function test_the_audio_file_must_be_a_supported_audio_format(): void
    {
        Transcription::fake();

        $this->post(
            route('assistant.transcribe'),
            ['audio' => UploadedFile::fake()->create('notes.txt', 1, 'text/plain')],
            ['Accept' => 'application/json'],
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('audio');

        Transcription::assertNothingGenerated();
    }

    public function test_browser_recording_mime_types_pass_validation(): void
    {
        foreach (['audio/webm', 'video/webm'] as $mimeType) {
            $response = $this->post(
                route('assistant.transcribe'),
                ['audio' => UploadedFile::fake()->create('voice.webm', 200, $mimeType)],
                ['Accept' => 'application/json'],
            );

            $response->assertSessionHasNoErrors();

            $this->assertNotSame(
                422,
                $response->getStatusCode(),
                "A browser recording with the {$mimeType} MIME type must pass validation.",
            );
        }
    }

    public function test_guests_can_generate_spoken_audio(): void
    {
        Audio::fake([base64_encode('fake-wav-bytes')]);

        $response = $this->postJson(route('assistant.speak'), [
            'text' => 'مرحبا',
            'voice' => 'female',
            'language' => 'ar-SA',
        ]);

        $response->assertOk();

        $this->assertSame('fake-wav-bytes', $response->getContent());
        $this->assertStringStartsWith('audio/', (string) $response->headers->get('Content-Type'));

        Audio::assertGenerated(
            fn (AudioPrompt $prompt): bool => $prompt->contains('مرحبا') && $prompt->isFemale(),
        );
    }

    public function test_the_text_is_required_before_generating_speech(): void
    {
        Audio::fake();

        $this->postJson(route('assistant.speak'), ['voice' => 'female'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('text');

        Audio::assertNothingGenerated();
    }

    public function test_the_voice_must_be_male_or_female_before_generating_speech(): void
    {
        Audio::fake();

        $this->postJson(route('assistant.speak'), [
            'text' => 'مرحبا',
            'voice' => 'robot',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('voice');

        Audio::assertNothingGenerated();
    }

    public function test_speech_failures_return_a_generic_arabic_error(): void
    {
        Audio::fake(fn () => throw new RuntimeException('Provider exploded with secret detail.'));

        $response = $this->postJson(route('assistant.speak'), [
            'text' => 'مرحبا',
            'voice' => 'female',
        ]);

        $response->assertStatus(503)
            ->assertJsonStructure(['error'])
            ->assertJsonMissingPaths(['exception', 'trace', 'file', 'line']);

        $this->assertMatchesRegularExpression('/\p{Arabic}/u', $response->json('error'));
        $this->assertStringNotContainsString('Provider exploded', $response->getContent());
        $this->assertStringNotContainsString('RuntimeException', $response->getContent());
    }
}
