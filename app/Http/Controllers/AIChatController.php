<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\Request;

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

        $answer = $this->aiService->chat($data['message'], $context);

        return response()->json(['answer' => $answer]);
    }
}
