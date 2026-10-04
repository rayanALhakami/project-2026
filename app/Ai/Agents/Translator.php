<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

class Translator implements Agent
{
    use Promptable;

    /**
     * Create a new translator agent.
     */
    public function __construct(
        public string $targetLanguage,
        public ?string $sourceLanguage = null,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $source = $this->sourceLanguage === null
            ? 'Detect the source language from the text itself.'
            : "The source language is most likely {$this->sourceLanguage}; treat it as a hint only and trust the text when they disagree.";

        return <<<PROMPT
You are a professional translator.

Translate the user's text into the target language: {$this->targetLanguage}.

Rules:
- {$source}
- Preserve meaning, tone, names, numbers, and place names exactly. Keep proper nouns in their commonly used form in the target language.
- Return ONLY the translated text: no explanations, no quotes, no markdown, no labels, and no notes.
- If the input is already in the target language, return it polished and natural in that language.
PROMPT;
    }
}
