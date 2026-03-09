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
    private function serializeUser(User $user): array
    {
        return [
            'public_id' => $user->public_id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    public function register(array $data): array
    {
        // Hash the password (like Python's passlib/bcrypt)
        $data['password'] = Hash::make($data['password']);
        
        // Default role if not provided
        $data['role'] = $data['role'] ?? 'developer';

        $user = User::create($data);
        $token = $user->createToken('api_token')->plainTextToken;

        return ['user' => $this->serializeUser($user), 'token' => $token];
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
        'user' => $this->serializeUser($user),
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
                'email' => [$this->passwordStatusMessage($status)],
            ]);
        }

        return ['message' => 'Password reset link sent successfully.'];
    }

    public function resetPassword(array $data): array
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
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
                $this->passwordStatusField($status) => [$this->passwordStatusMessage($status)],
            ]);
        }

        return ['message' => 'Password has been reset successfully.'];
    }

    private function passwordStatusMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER => 'No account found with that email address.',
            Password::RESET_THROTTLED => 'Please wait before requesting another reset link.',
            Password::INVALID_TOKEN => 'This reset token is invalid or has expired.',
            default => __($status),
        };
    }

    private function passwordStatusField(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'token',
            default => 'email',
        };
    }
}