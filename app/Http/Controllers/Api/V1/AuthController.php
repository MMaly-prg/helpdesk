<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/token
     * Generuj token Sanctum dla systemu zewnętrznego lub serwisanta.
     */
    public function token(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_name' => 'required|string|max:100',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', true)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Nieprawidłowe dane logowania.'],
            ]);
        }

        // Jedno urządzenie = jeden token (opcjonalnie – skasuj stary)
        $user->tokens()->where('name', $request->device_name)->delete();

        $token = $user->createToken($request->device_name, [
            'tickets:read',
            'tickets:write',
            'reports:read',
        ]);

        $user->update(['last_login_at' => now()]);

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user'       => [
                'id'   => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * DELETE /api/v1/auth/token
     * Unieważnij bieżący token.
     */
    public function revoke(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Token unieważniony.']);
    }
}
