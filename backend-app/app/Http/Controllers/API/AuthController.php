<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    function signup(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|max:10|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ]);

        return response([
            'message' => 'User signed up successfully',
            'user' => $user
        ], 201);
    }

    function signin(Request $request){
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:6|max:10'
        ]);

        $user = User::where('email', $request->email)->first();

        if(!Hash::check($request->password, $user->password)){
            throw ValidationException::withMessages([
                'password' => 'The provided password is incorrect',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'message' => 'User signed in successfully',
            'user' => $user,
            'token' => $token
        ], 200);
    }

    function signout(Request $request){
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return response([
            'message' => 'User signout successfully'
        ], 200);
    }

    function verify(Request $request){
        return response([
            'message' => 'Token is valid',
            'user' => $request->user()
        ], 200);
    }
}
