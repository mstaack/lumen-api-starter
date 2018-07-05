<?php

namespace App\Http\Controllers;

use App\User;
use App\VerifyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyMail;

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

        $user = User::where('email', $credentials['email']);
        if ( $user->exists() ) {
            if ( $user->first()->verified ) {
                $token = Auth::attempt($credentials);
                    if ( $token ) {
                        return response([
                            'status' => 'success',
                            'data' => ['refresh_token' => $user->first()->refresh_token]
                        ])
                        ->header('Authorization', $token);
                    }
                    else {
                        return response([
                            'status' => 'invalid.credentials',
                            'message' => 'Invalid credentials.'
                        ], 400);
                    }
            }
            else {
                return response([
                    'status' => 'unverified.account',
                    'message' => 'Please verify your email address.'
                ], 400);
            }
        }
        return response([
            'status' => 'invalid.credentials',
            'message' => 'Invalid credentials.'
        ], 400);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $emailInUse = User::where('email', $request->input('email'))->exists();

        if (!$emailInUse) {
            $user = new User;
            $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->save();

            $verifyUser = VerifyUser::create([
                'user_id' => $user->id,
                'token' => str_random(60)
            ]);

            Mail::to($user->email)->send(new VerifyMail($user));
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Check email to verify account'
        ]);
    }

    /**
    * @param String $token
    *
    * @return Response
    */
    public function verify($token)
    {
        $verifyUser = VerifyUser::where('token', $token)->first();
        if(isset($verifyUser) ){
            $verifyUser->user->verified = 1;
            $verifyUser->user->save();
            $verifyUser->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'verified'
            ]);
        }
        else {
            return response()->json([
                'status' => 'invalid.token',
                'message' => 'Invalid verification token'
            ], 400);
        }
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
                'status' => 'invalid.refresh_token',
                'message' => 'Invalid refresh token.'
            ], 400);
        }
    }
}
