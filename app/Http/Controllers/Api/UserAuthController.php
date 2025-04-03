<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserAuthController extends Controller
{
    //todo: validator
    public function register(Request $request)
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

}
