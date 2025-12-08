<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return Response::json(['error' => 'Could not create token'], 500);
        }

        return Response::json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return Response::json(['error' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return Response::json(['error' => 'Could not create token'], 500);
        }

        return Response::json([
            'token' => $token,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ], 200);
    }

    public function refresh()
    {
        try {
            $newToken = auth()->refresh();

            return Response::json([
                'message' => 'Token refreshed successfully',
                'token'   => $newToken
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'error' => 'Token is invalid or expired'
            ], 401);
        }
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            return Response::json(['error' => 'Failed to logout, please try again'], 500);
        }

        return Response::json(['message' => 'Successfully logged out']);
    }

}
