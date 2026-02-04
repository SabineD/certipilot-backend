<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;
use OpenAI\Exceptions\RateLimitException;

class AIChatController extends Controller
{
    public function __construct(private AIService $aiService)
    {
    }

    public function chat(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = $request->user();

        $context = [
            'company_id' => $user->company_id,
            'role' => $user->role,
        ];

        try {
            $answer = $this->aiService->chat($data['message'], $context);
        } catch (RateLimitException) {
            return response()->json([
                'message' => 'De AI-dienst is tijdelijk overbelast. Probeer later opnieuw.',
            ], 429);
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Er ging iets mis bij het ophalen van een antwoord.',
            ], 500);
        }

        return response()->json(['answer' => $answer]);
    }
}
