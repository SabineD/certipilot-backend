<?php

namespace App\Services;

use App\AI\CertiPilotSystemPrompt;
use OpenAI\Laravel\Facades\OpenAI;

class AIService
{
    public function chat(string $userMessage, array $context): string
    {
        $contextPayload = $this->encodeContext($context);

        try {
            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => CertiPilotSystemPrompt::get()],
                    ['role' => 'system', 'content' => 'Context: ' . $contextPayload],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.2,
                'max_tokens' => 600,
            ]);

            $content = $response->choices[0]->message->content ?? '';

            if (! is_string($content) || trim($content) === '') {
                return 'Ik kon geen antwoord genereren. Probeer het opnieuw of stel je vraag iets anders.';
            }

            return trim($content);
        } catch (\Throwable $e) {
            logger('AIService error', [
                'message' => $e->getMessage(),
            ]);

            return 'Er ging iets mis bij het ophalen van een antwoord. Probeer het later opnieuw.';
        }
    }

    private function encodeContext(array $context): string
    {
        try {
            return json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException) {
            return '{}';
        }
    }
}