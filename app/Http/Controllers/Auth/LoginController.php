<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
        ], [], [
            'login' => 'email atau nomor HP',
            'password' => 'kata sandi',
        ]);

        // Boleh masuk dengan email atau nomor HP
        $kolom = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'no_hp';
        $user = User::where($kolom, $data['login'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'login' => 'Email/nomor HP atau kata sandi salah.',
            ]);
        }

        $pesanStatus = match ($user->status) {
            'pending' => 'Akun Anda masih menunggu verifikasi admin. Kami kirim notifikasi setelah disetujui.',
            'ditolak' => 'Pendaftaran akun ini ditolak admin. Hubungi admin untuk informasi lebih lanjut.',
            'nonaktif' => 'Akun ini sedang dinonaktifkan. Hubungi admin untuk mengaktifkannya kembali.',
            default => null,
        };

        if ($pesanStatus) {
            throw ValidationException::withMessages(['login' => $pesanStatus]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isPengelolaTps()) {
            return redirect()->route('pengelola.dashboard');
        }

        if ($user->isKurir()) {
            return redirect()->route('kurir.dashboard');
        }

        return redirect()->route('pengguna.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }
}

