<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        // Hash the password (like Python's passlib/bcrypt)
        $data['password'] = Hash::make($data['password']);
        
        // Default role if not provided
        $data['role'] = $data['role'] ?? 'developer';

        $user = User::create($data);
        $token = $user->createToken('api_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(array $data): array
{
    $user = User::where('email', $data['email'])->first();
    
    if (!$user || !Hash::check($data['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Invalid credentials.'],
        ]);
    }

    return [
        'user' => $user,
        'token' => $user->createToken('api_token')->plainTextToken
    ];
}

    public function logout(User $user): void
    {
        // This targets the specific token used to authenticate this request
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
    }

    public function sendResetLink(array $data): array
    {
        $status = Password::sendResetLink([
            'email' => $data['email'],
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return ['message' => __($status)];
    }

    public function resetPassword(array $data): array
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return ['message' => __($status)];
    }
}