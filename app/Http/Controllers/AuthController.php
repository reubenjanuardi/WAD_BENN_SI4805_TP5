<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        /**
         * ==========1===========
         * Validasi data registrasi yang masuk
         */
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please check your request',
                'errors' => $validator->errors(),
            ], 422);
        }

        /**
         * =========2===========
         * Buat user baru dan generate token API dengan masa berlaku 1 jam
         */
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Token expire 1 jam
        $token = $user->createToken('auth_token', ['*'], now()->addHour())->plainTextToken;

        /**
         * =========3===========
         * Response sukses
         */
        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }


    public function login(Request $request)
    {
        /**
         * =========4===========
         * Validasi request login
         */
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please check your request',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Cek kredensial
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();

        /**
         * =========5===========
         * Token expired 1 jam
         */
        $token = $user->createToken('auth_token', ['*'], now()->addHour())->plainTextToken;

        /**
         * =========6===========
         * Response sukses
         */
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ], 200);
    }


    public function logout(Request $request)
    {
        /**
         * =========7===========
         * Hapus token yang sedang dipakai
         */
        $request->user()->currentAccessToken()->delete();

        /**
         * =========8===========
         * Response sukses
         */
        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
