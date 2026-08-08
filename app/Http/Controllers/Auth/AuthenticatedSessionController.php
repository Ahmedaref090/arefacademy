<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    // Long-lived cookie lifetime (minutes) — ~5 years.
    protected const DEVICE_COOKIE_LIFETIME_MINUTES = 60 * 24 * 365 * 5;

    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'phone' => ['required', new Phone],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'phone' => __('auth.failed'),
            ]);
        }

        $user = $request->user();

        // Only the admin can block a student's account (manual "Prevent Login").
        // Blocked students see the locale-aware message below; admins are never blocked.
        if ($user->isLoginBlocked()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'phone' => __('auth.login_blocked'),
            ]);
        }

        // Track the device (cookie-based). There is no automatic device cap
        // anymore — admins may use unlimited devices and blocking is manual.
        $this->registerDevice($request, $user);

        $request->session()->regenerate();

        // Record the login for the "Security & Login History" page.
        $user->loginHistories()->create([
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        return redirect()->intended(
            $user->isAdmin() ? route('admin.dashboard') : route('dashboard')
        );
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Register (or refresh) the device the user is logging in from, using the
     * long-lived encrypted device_uuid cookie. Dynamic IP changes are
     * irrelevant because the device identity lives in the cookie, not the IP.
     *
     * - Known cookie (exists in the user's devices): refresh last_active_at.
     * - Unknown cookie / no cookie: register it and issue a new device_uuid.
     *
     * There is no device-count cap anymore: only the admin can block login,
     * so devices are tracked purely for monitoring on the student profile.
     */
    protected function registerDevice(Request $request, User $user): void
    {
        $deviceUuid = $request->cookie('device_uuid');

        if ($deviceUuid !== null) {
            $device = $user->devices()->where('device_uuid', $deviceUuid)->first();

            if ($device !== null) {
                $device->update([
                    'device_name' => $this->deviceNameFrom($request),
                    'last_seen_ip' => $request->ip(),
                    'last_active_at' => now(),
                ]);

                return;
            }
        }

        $uuid = (string) Str::uuid();

        $user->devices()->create([
            'device_uuid' => $uuid,
            'device_name' => $this->deviceNameFrom($request),
            'last_seen_ip' => $request->ip(),
            'last_active_at' => now(),
        ]);

        // The cookie is encrypted automatically by EncryptCookies. Queue it so
        // it is attached to the redirect response.
        Cookie::queue(Cookie::make(
            'device_uuid',
            $uuid,
            self::DEVICE_COOKIE_LIFETIME_MINUTES,
            '/',
            null,
            false,
            true
        ));
    }

    protected function deviceNameFrom(Request $request): string
    {
        return substr((string) $request->userAgent(), 0, 255);
    }
}
