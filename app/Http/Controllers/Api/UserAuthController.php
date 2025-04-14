<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\Bridge\AccessToken;
use Symfony\Component\HttpFoundation\Response;

class UserAuthController extends Controller
{
    //creates the user
    public function register(RegisterRequest  $request){

        $user = User::create($request->validated());

        return response()->json([
            'status' => true,
            'message'=> "User Registered Successfully",
            'data' => $user->makeHidden('password')
        ]);
    }

    //generates the token
    public function login(LoginRequest $request){

        $userInfo = $request->validate([
            'email'     => 'required|email|exists:users,email',
            'password'   => 'required|string',
        ]);
        
        //email existing in the db, authenticate email and password then generate a token 
        $user = User::where('email', $userInfo['email'])->first();

        if (!$user || !Hash::check($userInfo['password'], $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken("Auth api")->accessToken;

        return response()->json([
            'status' => true,
            'message' => "User Logged In Successfully",
            'user' => $user->only(['id', 'name', 'email', 'role', 'phone']),
            'token' => $token
        ], 200);
    }

    //for generation of new token in place of old token
    public function refreshToken(){

        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Revoke all tokens for this user
        $tokens = $user->tokens;
        foreach ($tokens as $token) {
            $token->revoke();
        }

        $token = $user->createToken("Auth API")->accessToken;

            return response()->json([
                'status'=> true,
                'message'=> "Token Re-issued Successfully",
                'token' => $token
            ]);
        
    }


    public function logout(LogoutRequest $request): JsonResponse{
        
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Already logged out', 
            ]);
        }

        // Revoke all tokens for this user
        foreach ($user->tokens as $token) {
            $token->revoke();
        }

        return response()->json([
            'status'=> true,
            'message'=> "User Logged Out Successfully"
        ], 200);
    }
    
}
