<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken($this->sessionName($request))->accessToken;

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized. Invalid email or password',
            ], 401);
        }

        $user = Auth::user();

        $token = $user->createToken($this->sessionName($request))->accessToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    private function sessionName(Request $request): string
    {
        $agent = $request->userAgent() ?? '';

        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Microsoft Edge',
            str_contains($agent, 'Firefox/') => 'Firefox',
            str_contains($agent, 'Chrome/') && ! str_contains($agent, 'Edg/') => 'Chrome',
            str_contains($agent, 'Safari/') && ! str_contains($agent, 'Chrome/') => 'Safari',
            default => 'Браузер',
        };

        $platform = match (true) {
            str_contains($agent, 'iPhone'), str_contains($agent, 'iPad') => 'iOS',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'Macintosh'), str_contains($agent, 'Mac OS') => 'macOS',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Linux') => 'Linux',
            default => 'устройство',
        };

        return "Microstore — {$browser} · {$platform}";
    }
}
