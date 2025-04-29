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
/**
 * @OA\Post(
 *     path="/register",
 *     tags={"Authentication"},
 *     summary="Register a new user",
 *     operationId="registerUser",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User registered successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User Registered Successfully"),
 *             @OA\Property(property="data", ref="#/components/schemas/User")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        return response()->json([
            'status' => true,
            'message'=> "User Registered Successfully",
            'data' => $user->makeHidden('password')
        ]);
    }

/**
 * @OA\Post(
 *     path="/login",
 *     tags={"Authentication"},
 *     summary="Authenticate user and generate token",
 *     operationId="loginUser",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User logged in successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User Logged In Successfully"),
 *             @OA\Property(property="user", ref="#/components/schemas/User"),
 *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Invalid Entry")
 *         )
 *     )
 * )
 */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Entry'
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

/**
 * @OA\Post(
 *     path="/refreshToken",
 *     tags={"Authentication"},
 *     summary="Refresh authentication token",
 *     operationId="refreshToken",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Token refreshed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Token Re-issued Successfully"),
 *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated")
 *         )
 *     )
 * )
 */

    public function refreshToken()
    {
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

/**
 * @OA\Post(
 *     path="/logout",
 *     tags={"Authentication"},
 *     summary="Logout user and revoke token",
 *     operationId="logoutUser",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="User logged out successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="User Logged Out Successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Already logged out")
 *         )
 *     )
 * )
 */

    public function logout(LogoutRequest $request)
    {
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
