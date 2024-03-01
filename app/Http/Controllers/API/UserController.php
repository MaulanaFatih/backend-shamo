<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:254'],
                'phone' => ['nullable', 'string', 'max:254'],
                'username' => ['required', 'string', 'max:253', 'unique:users'],
                'email' => ['required', 'string', 'max:255', 'email', 'unique:users'],
                'password' => ['required', 'string', 'max:255', new Password],
            ]);

            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            $user = User::where('email', $request->email)->first();
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Successfully Registered');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Register User Failed', 500);
        }
        return 'kelaz';
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required'],
                'password' => ['required'],
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthenticated',
                ], 'Auth Failed', 500);
            }
            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception("invalid Credentials");
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication User Failed', 500);
        }
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(), 'User profile');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();

        $user->update($data);

        return ResponseFormatter::success($request->$user, 'User profile updated');
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }
}
