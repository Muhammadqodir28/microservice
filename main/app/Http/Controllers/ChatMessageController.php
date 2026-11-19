<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\ReverbBroadcaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ChatMessageController extends Controller
{
    public function index(): JsonResponse
    {
        $messages = ChatMessage::query()
            ->with('user:id,name')
            ->latest('id')
            ->limit(80)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (ChatMessage $message) => $this->present($message));

        return response()->json(['messages' => $messages]);
    }

    public function store(Request $request, ReverbBroadcaster $broadcaster): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = ChatMessage::query()->create([
            'user_id' => $request->user()->id,
            'body' => trim($validated['body']),
        ])->load('user:id,name');

        try {
            $broadcaster->broadcast('chat.message.sent', ['private-chat.global'], [
                'message' => $this->present($message),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }

        return response()->json(['message' => $this->present($message)], 201);
    }

    /** @return array<string, mixed> */
    private function present(ChatMessage $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'created_at' => $message->created_at?->toISOString(),
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
            ],
        ];
    }
}
