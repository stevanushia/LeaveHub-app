<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Generate Personal Access Token (PAT)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang digunakan
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);
    }

    public function impersonate(Request $request, $userId)
    {
        $admin = $request->user();

        // Kita tambahkan email dan role ke pesan error untuk proses investigasi
        if ($admin->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. Saya terbaca sebagai role: ' . $admin->role,
                'email' => $admin->email
            ], 403);
        }

        $targetUser = \App\Models\User::findOrFail($userId);

        $token = $targetUser->createToken('impersonate_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $targetUser
        ]);
    }
}
