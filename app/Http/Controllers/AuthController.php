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

        if ( $token ) {
            $user = User::where('email', $credentials['email']);
            if ( $user->exists() ) {
                return response([
                    'status' => 'success',
                    'refresh_token' => $user->first()->refresh_token
                ])
                ->header('Authorization', $token);
            }
            else {
                return response([
                    'status' => 'error',
                    'error' => 'invalid.credentials',
                    'message' => 'Invalid credentials.'
                ], 400);
            }
        }
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

    /**
    * @param Request $request
    *
    * @return Response
    */

    public function refresh(Request $request)
    {
        if ( $token = Auth::refresh($request) ) {
            return response([
                'status' => 'success'
            ])
            ->header('Authorization', $token);
        }
        else {
            return response([
                'status' => 'error',
                'error' => 'invalid.refresh_token',
                'message' => 'Invalid refresh token.'
            ], 400);
        }
    }
}
