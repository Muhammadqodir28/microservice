<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

class UserController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json(['user' => $request->user()]);
    }

    public function detail(Request $request): JsonResponse
    {
        return $this->show($request);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->fill($validated)->save();

        return response()->json([
            'message' => 'Профиль сохранён.',
            'user' => $user->fresh(),
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password:api'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return response()->json(['message' => 'Пароль успешно изменён.']);
    }

    public function sessions(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->token()?->id;

        $sessions = $request->user()->tokens()
            ->where('revoked', false)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'created_at', 'updated_at', 'expires_at'])
            ->map(fn (Token $token) => [
                'id' => $token->id,
                'name' => $token->name ?: 'Сеанс Microstore',
                'created_at' => $token->created_at,
                'expires_at' => $token->expires_at,
                'is_current' => (string) $token->id === (string) $currentTokenId,
            ])
            ->values();

        return response()->json(['sessions' => $sessions]);
    }

    public function revokeSession(Request $request, string $tokenId): JsonResponse
    {
        $token = $request->user()->tokens()->whereKey($tokenId)->firstOrFail();
        $this->revokeToken($token);

        return response()->json(['message' => 'Сеанс завершён.']);
    }

    public function revokeOtherSessions(Request $request): JsonResponse
    {
        $currentTokenId = $request->user()->token()?->id;
        $tokens = $request->user()->tokens()
            ->where('revoked', false)
            ->when($currentTokenId, fn ($query) => $query->where('id', '!=', $currentTokenId))
            ->get();

        foreach ($tokens as $token) {
            $this->revokeToken($token);
        }

        return response()->json([
            'message' => 'Все остальные сеансы завершены.',
            'revoked_count' => $tokens->count(),
        ]);
    }

    private function revokeToken(Token $token): void
    {
        $token->revoke();
        Passport::refreshToken()->newQuery()
            ->where('access_token_id', $token->id)
            ->update(['revoked' => true]);
    }
}
