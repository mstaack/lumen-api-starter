<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Class AuthController
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getUser()
    {
        return response()->json(Auth::user());
    }

    /**
     * @param LoginRequest $request
     *
     * @return JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json(['user' => Auth::user(), 'token' => $token]);
    }

    /**
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $name = $request->input('name');

        $user = User::createFromValues($name, $email, $password);

        $token = Auth::attempt(compact('email', 'password'));

        return response()->json(['user' => $user, 'token' => $token]);
    }

    /**
     * @param String $token
     *
     * @return Response
     * @throws Exception
     */
    public function verify($token)
    {
        $user = User::verifyToken($token);

        if (!$user) {
            return response()->json(['message' => 'Invalid verification token'], 400);
        }

        return response()->json(['message' => 'Account has been verified']);
    }
}
