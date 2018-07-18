<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RecoverPasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Mail\PasswordReset;
use App\Mail\Welcome;
use App\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

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

        Mail::to($user)->send(new Welcome($user));

        return response()->json(['message' => 'Account created. Please verify via email.']);
    }

    /**
     * @param String $token
     *
     * @return Response
     * @throws Exception
     */
    public function verify($token)
    {
        $user = User::verifyByToken($token);

        if (!$user) {
            return response()->json(['message' => 'Invalid verification token'], 400);
        }

        return response()->json(['message' => 'Account has been verified']);
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function forgotPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|exists:users,email'
        ]);

        $user = User::byEmail($request->input('email'));

        Mail::to($user)->send(new PasswordReset($user));
    }

    /**
     * @param Request $request
     * @param $token
     * @throws ValidationException
     */
    public function recoverPassword(Request $request, $token)
    {
        $this->validate($request, [
            'password' => 'required|min:8',
        ]);

        User::newPasswordByResetToken($token, $request->input('password'));
    }
}
