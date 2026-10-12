<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ArtisanProfile;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user()->role);
        }
        return view('auth.karyalokal-login');
    }

    public function showRegister()
    {
        if (Auth::check() && !Auth::user()->isCustomer()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (Auth::check() && !Auth::user()->isCustomer()) {
            return $this->redirectByRole(Auth::user()->role);
        }

        $guestCartSessionId = $request->session()->get('cart_session_id', $request->session()->getId());
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:customer,artisan'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'role' => $data['role'],
            'status_akun' => 'aktif',
        ]);

        if ($user->isArtisan()) {
            ArtisanProfile::create([
                'user_id' => $user->id,
                'city' => $data['city'] ?? null,
                'province' => $data['province'] ?? null,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $this->transferGuestCart($user, $guestCartSessionId);
        $request->session()->forget('cart_session_id');

        return $this->redirectByRole($user->role);
    }

    public function login(Request $request)
    {
        $guestCartSessionId = $request->session()->get('cart_session_id', $request->session()->getId());
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Cek status akun
            if ($user->status_akun !== 'aktif') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda ' . $user->status_akun . '. Hubungi administrator.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            $this->transferGuestCart($user, $guestCartSessionId);
            $request->session()->forget('cart_session_id');

            return $this->redirectByRole($user->role);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectByRole(string $role)
    {
        return match($role) {
            'admin'            => redirect()->route('admin.dashboard'),
            'artisan'          => redirect()->route('artisan.dashboard'),
            'curator'          => redirect()->route('curator.dashboard'),
            'customer_service' => redirect()->route('support.dashboard'),
            default            => redirect()->route('home'),
        };
    }

    private function transferGuestCart(User $user, string $sessionId): void
    {
        Cart::whereNull('user_id')
            ->where('session_id', $sessionId)
            ->update(['user_id' => $user->id, 'session_id' => null]);
    }
}
