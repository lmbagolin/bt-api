<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse
    {
        // Sempre retorna 200 independente de o e-mail existir ou não (previne enumeração)
        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'Se este e-mail estiver cadastrado, você receberá as instruções em breve.',
        ], 200);
    }

    /**
     * Reset the given user's password.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['message' => __($status)], 422);
    }
}
