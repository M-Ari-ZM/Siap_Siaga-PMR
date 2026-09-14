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
            'nomor_induk' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt(['nomor_induk' => $credentials['nomor_induk'], 'password' => $credentials['password']], $remember)) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'nomor_induk' => 'Nomor Induk (NIS/NIP) atau password salah.',
        ])->onlyInput('nomor_induk');
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
            'nomor_induk' => ['required', 'string', 'max:50', 'unique:users,nomor_induk'],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'nomor_induk.unique' => 'Nomor Induk (NIS/NIP) ini sudah terdaftar.',
            'nomor_induk.required' => 'Nomor Induk (NIS/NIP) wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $user = User::create([
            'nomor_induk' => $validated['nomor_induk'],
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'role' => 'student', // Semua pendaftaran mandiri otomatis menjadi warga sekolah (Siswa/Guru/Pelapor)
            'password' => Hash::make($validated['password']),
        ]);

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
