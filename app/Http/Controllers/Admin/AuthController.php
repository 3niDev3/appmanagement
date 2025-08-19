<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // Check if user is already logged in
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $this->checkTooManyFailedAttempts($request);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Add any additional conditions like is_active if needed
        // $credentials['is_active'] = true;

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $this->clearLoginAttempts($request);
            
            // Update last login info
            $admin = Auth::guard('admin')->user();
            $admin->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Set remember token properly if remember is checked
            if ($remember) {
                Auth::guard('admin')->login($admin, true);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        $this->incrementLoginAttempts($request);

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        try {
            // Clear remember token
            if (Auth::guard('admin')->check()) {
                $user = Auth::guard('admin')->user();
                $user->remember_token = null;
                $user->save();
            }

            Auth::guard('admin')->logout();
        } catch (\Exception $e) {
            // ignore if already logged out
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear any remember cookies
        Cookie::queue(Cookie::forget('remember_admin_' . sha1(config('app.key'))));

        if (Route::has('admin.login')) {
            return redirect()->route('admin.login');
        }

        // Fallback if somehow route not found
        return redirect('/admin/login');
    }

    protected function checkTooManyFailedAttempts(Request $request)
    {
        $key = $this->throttleKey($request);
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }
    }

    protected function incrementLoginAttempts(Request $request)
    {
        RateLimiter::hit($this->throttleKey($request), 300); // 5 minutes
    }

    protected function clearLoginAttempts(Request $request)
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    protected function throttleKey(Request $request)
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }
}