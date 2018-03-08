<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

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
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
            'name' => 'string'
        ]);

        $emailInUse = User::where('email', $request->input('email'))->exists();

        if ($emailInUse) {
            return response()->json(['message' => 'Email already in use!'], 400);
        }

        $user = new User;
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->name = $request->input('name');

        $user->save();

        $token = Auth::attempt($request->only(['email', 'password']));

        return response()->json(['token' => $token, 'user' => $user]);
    }
}
