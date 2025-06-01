<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();

                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => true,
                    'message' => 'Login successful',
                    'token' => $token,
                    'user' => $user
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Invalid email or password'
            ], 401);
        } catch (\Exception $e) {
            Log::error('Login Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'An error occurred'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    
        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
    


    public function dashboard(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Welcome to your dashboard',
            'user' => $request->user()
        ]);
    }
}

