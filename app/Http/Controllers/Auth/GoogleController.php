<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    /**
     * Redirect to Google's OAuth page
     */
    public function redirect()
    {
        try {
            Log::info('Starting Google authentication redirect');
            return Socialite::driver('google')
                ->redirect();
        } catch (\Exception $e) {
            Log::error('Google authentication redirect error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle the Google authentication callback
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback()
    {
        try {
            Log::info('Handling Google authentication callback');

            $googleUser = Socialite::driver('google')->user();
            
            Log::info('Google user authenticated', [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail()
            ]);

            // Find or create a new user
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName() ?? explode('@', $googleUser->getEmail())[0],
                    'email_verified_at' => now(), // Google accounts are already verified
                    'password' => bcrypt(Str::random(16)), // Random password for OAuth users
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar()
                ]
            );

            Log::info('User authenticated successfully', ['id' => $user->id]);

            Auth::login($user);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error('Google authentication callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Unable to login with Google. Please try again.');
        }
    }
}
