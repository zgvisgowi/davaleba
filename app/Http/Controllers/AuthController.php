<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'forgotPassword', 'resetPassword']]);
    }

    public function register(Request $request): JsonResponse
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|min:2',
            'email' => 'required|unique:users,email|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)]
        ]);

        if (!$validated->fails()) {
            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password'))
            ]);

            $user->cart()->create();

            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => $validated->errors()->first()]);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($token = Auth::attempt($credentials)) {
            return response()->json(['success' => true, 'authorisation' => [
                'token' => $token,
                'type' => 'bearer'
            ]]);
        } else {
            return response()->json(['success' => false, 'message' => 'The provided credentials do not match our records.']);
        }
    }

    public function logout(): JsonResponse
    {
        Auth::logout();
        return response()->json(['success' => false, 'message' => 'Successfully logged out.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === password::RESET_LINK_SENT) {
            return response()->json(['success' => true, 'status' => __($status)]);
        }
        return response()->json(['success' => false, 'status' => __($status)]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['success' => true, 'status' => __($status)]);
        }
        return response()->json(['success' => false, 'status' => __($status)]);
    }
}
