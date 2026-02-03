<?php

namespace App\Services;

use App\AI\CertiPilotSystemPrompt;
use OpenAI\Laravel\Facades\OpenAI;

class AIService
{
    public function chat(string $userMessage, array $context): string
    {
        $contextPayload = $this->encodeContext($context);

        $response = OpenAI::chat()->create([
            'model' => config('services.openai.model', 'gpt-4o-mini'),
            'messages' => [
                ['role' => 'system', 'content' => CertiPilotSystemPrompt::get()],
                ['role' => 'system', 'content' => $contextPayload],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => 0.2,
        ]);

        $content = $response->choices[0]->message->content ?? '';

        return trim($content);
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
