<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginHistory;
use App\Models\User;
use App\Traits\FlashMessages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use FlashMessages;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        $user = User::where('phone', $login)
            ->orWhere('email', $login)
            ->first();

        if ($user && Auth::attempt(['id' => $user->id, 'password' => $password], $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            LoginHistory::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'login_at' => now(),
                'successful' => true,
            ]);

            $this->success('Welcome back, ' . $user->name . '!');

            $intended = redirect()->intended();
            if ($intended->getTargetUrl() === url('/')) {
                if ($user->isAdmin()) {
                    return redirect('/admin/dashboard');
                }
                if ($user->isApprovedMember()) {
                    return redirect('/member/dashboard');
                }
                if ($user->hasActiveApplication()) {
                    return redirect()->route('register.dashboard');
                }
                return redirect()->route('register.create');
            }
            return $intended;
        }

        LoginHistory::create([
            'user_id' => null,
            'email' => $login,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'login_at' => now(),
            'successful' => false,
        ]);

        throw ValidationException::withMessages([
            'login' => trans('auth.failed'),
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            LoginHistory::where('user_id', $user->id)
                ->whereNull('logout_at')
                ->latest('login_at')
                ->first()
                ?->update(['logout_at' => now()]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $this->info('You have been logged out successfully.');

        return redirect('/login');
    }
}
