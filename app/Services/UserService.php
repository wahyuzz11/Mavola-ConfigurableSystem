<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;


class UserService
{

    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 60;



    public function login(array $credentials, bool $remember = false, string $throttleKey = null): void
    {
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            throw new \Exception('Terlalu banyak percobaan login, silahkan coba lagi nanti');
        }

        $attempted = Auth::attempt([
            ...$credentials,
            fn($query) => $query->whereNull('deleted_at'),
        ], $remember);

        if (!$attempted) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);
            
            Log::info("Percobaan login gagal untuk email",['email' => $credentials['email']]);

            throw ValidationException::withMessages([
                'email' => "Kredensial salah.",
            ]);
        }

        RateLimiter::clear($throttleKey);

        /** @var User */
        $user  = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();
    
    }


    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
