<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);

        if (!$token) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json(['token' => $token, 'user' => Auth::user()]);
    }

    /**
     * Invalidate a token.
     *
     * @return Response
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Logout successful!']);
    }
}
