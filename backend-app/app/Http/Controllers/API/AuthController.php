<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\User\SendVerificationEmailRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\SignupRequest;
use App\Http\Requests\User\SigninRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    function signup(SignupRequest $request){

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ]);

        $user->sendEmailVerificationNotification($request->callback_url);

        return response([
            'message' => 'User signed up successfully',
            'user' => $user
        ], 201);
    }

    function signin(SigninRequest $request){

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

        function verifyEmail(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'Email is already verified.',
            ]);
        }

        $user->markEmailAsVerified();

        return response([
            'message' => 'Email verified successfully.'
        ], 200);
    }

    function sendVerificationEmail(SendVerificationEmailRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => 'Email is already verified.',
            ]);
        }

        $user->sendEmailVerificationNotification($request->callback_url);

        return response([
            'message' => 'Verification email resent.'
        ], 200);
    }
}
