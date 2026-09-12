<?php

namespace App\Http\Controllers;

use App\Models\PmrProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:20'],
            'role' => ['required', 'in:student,pmr'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'nisn_or_member_id' => ['nullable', 'string', 'max:50'],
            'class_grade' => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        // If registered as PMR, create profile
        if ($user->role === 'pmr') {
            PmrProfile::create([
                'user_id' => $user->id,
                'nisn_or_member_id' => $request->nisn_or_member_id ?? 'PMR-' . rand(1000, 9999),
                'class_grade' => $request->class_grade ?? 'XI Umum',
                'availability_status' => 'available',
                'is_on_duty' => true,
                'last_location_updated_at' => now(),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user);
    }

    public function quickLogin(Request $request)
    {
        $role = $request->query('as', 'student');
        $user = User::where('role', $role)->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            return $this->redirectBasedOnRole($user);
        }

        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar.');
    }

    protected function redirectBasedOnRole(User $user)
    {
        return match ($user->role) {
            'pmr' => redirect()->route('pmr.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }
}
