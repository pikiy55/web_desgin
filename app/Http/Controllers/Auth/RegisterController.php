<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\KurirProfil;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // admin tidak bisa mendaftar sendiri
            'role' => ['required', Rule::in([User::ROLE_PENGGUNA, User::ROLE_PENGELOLA_TPS, User::ROLE_KURIR])],
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'no_hp' => ['required', 'string', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/', 'unique:users,no_hp'],
            'password' => ['required', 'confirmed', Password::min(8)],

            // khusus kurir
            'jenis_kendaraan' => ['required_if:role,kurir', 'nullable', 'string', 'max:30'],
            'plat_nomor' => ['required_if:role,kurir', 'nullable', 'string', 'max:15'],
            'no_sim' => ['nullable', 'string', 'max:30'],
            'kapasitas_angkut_kg' => ['nullable', 'numeric', 'min:1', 'max:999999'],
        ], [
            'no_hp.regex' => 'Nomor HP harus nomor Indonesia, contoh 081234567890.',
            'required_if' => ':attribute wajib diisi untuk kurir.',
        ], [
            'role' => 'peran',
            'nama' => 'nama lengkap',
            'no_hp' => 'nomor HP',
            'password' => 'kata sandi',
            'jenis_kendaraan' => 'jenis kendaraan',
            'plat_nomor' => 'plat nomor',
            'no_sim' => 'nomor SIM',
            'kapasitas_angkut_kg' => 'kapasitas angkut',
        ]);

        // pengguna langsung aktif; pengelola_tps & kurir menunggu verifikasi admin
        $status = $data['role'] === User::ROLE_PENGGUNA ? 'aktif' : 'pending';

        $user = DB::transaction(function () use ($data, $status) {
            $user = User::create([
                'role' => $data['role'],
                'nama' => $data['nama'],
                'email' => $data['email'],
                'no_hp' => $data['no_hp'],
                'password_hash' => $data['password'], // di-hash otomatis lewat cast 'hashed'
                'status' => $status,
            ]);

            if ($data['role'] === User::ROLE_KURIR) {
                KurirProfil::create([
                    'user_id' => $user->user_id,
                    'jenis_kendaraan' => $data['jenis_kendaraan'],
                    'plat_nomor' => strtoupper($data['plat_nomor']),
                    'no_sim' => $data['no_sim'] ?? null,
                    'kapasitas_angkut_kg' => $data['kapasitas_angkut_kg'] ?? null,
                ]);
            }

            return $user;
        });

        if ($status === 'aktif') {
            return redirect()->route('login')->with('status',
                'Pendaftaran berhasil! Silakan masuk dengan akun Anda untuk mengakses dashboard.');
        }

        return redirect()->route('login')->with('status',
            'Pendaftaran terkirim. Akun '.($data['role'] === User::ROLE_KURIR ? 'kurir' : 'pengelola TPS')
            .' Anda akan aktif setelah diverifikasi admin.');
    }
}

