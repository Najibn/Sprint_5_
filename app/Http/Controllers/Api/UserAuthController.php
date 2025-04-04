<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class UserAuthController extends Controller
{
    //todo: validator  register user
    /*public function register(Request $request)
    {
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8',
            'role' => 'required|in:admin,customer,technician',
            'phone' => 'nullable|string'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null
        ]);

        return response()->json($user, 201);
    }
*/
     
    //todo: validator  register user
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => bcrypt($request->validated('password')),
            'role' => $request->validated('role'),
            'phone' => $request->validated('phone'),
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->only(['id', 'name', 'email', 'role'])
        ], Response::HTTP_CREATED);
    }

    //authenticate the user
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->validated())) {
            return response()->json([
                'message' => 'Invalid Entry'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Logged in successfully',
           'user' => auth()->user()->only(['id', 'name', 'email', 'role'])
        ]);
    }

    //logging out user
    public function logout(){
        Auth::logout();

        return response()->json(['message' => 'successfully logged out']);

    }



/*
   //authenticate the user 
    public function login(Request $request){

        $user_info = $request ->validate([

            'email'=> 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($user_info)) {

            return response()->json(['message' => 'invalid entry'], 401);
        }

        return response()->json(['message' => 'logged in successfully']);

    }
*/
}
