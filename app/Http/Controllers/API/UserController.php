<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ["required", "string", "max:255"],
                'username' => ["required", "string", "max:255", "unique:users"],
                'phone' => ["nullable", "string", "max:255"],
                'email' => ["required", "string", "email", "max:255", "unique:users"],
                'password' => ["required", "string", new Password],
            ]);

            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => "Bearer",
                    'user' => $user
                ],
                "Registrasi Berhasil"
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                [
                    'message' => "Something went wrong",
                    'error' => $e
                ],
                "Registrasi Gagal",
                500
            );
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => "email|required",
                'password' => "required",
            ]);

            $credentials = request(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error(
                    [
                        'message' => "Unauthorized"
                    ],
                    "Authentication Failed",
                    401,
                );
            }

            $user = User::where('email', $request->email)->first();

            if (!Hash::check($request->password, $user->password)) {
                throw new Exception("Invalid Credentials");
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success(
                [
                    'access_token' => $tokenResult,
                    'token_type' => "Bearer",
                    'user' => $user,
                ],
                "Authentication Success"
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                [
                    'message' => "Something went wrong",
                    'error' => $e,
                ],
                "Authentication Failed",
                401,
            );
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(), "Data profile user berhasil diambil"
        );
    }
}
