@extends('layouts.kurir')

@section('title', 'Profil Kurir & Kendaraan')
@section('heading', 'Profil Kurir & Kendaraan Operasional')
@section('subheading', 'Kelola data diri, nomor kontak operasional, serta spesifikasi kendaraan angkut sampah Anda.')

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- KOLOM KIRI (2 Kolom): FORM EDIT PROFIL & KENDARAAN --}}
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white border border-stone-200 rounded-xl p-6 space-y-6">
                <div>
                    <h2 class="font-judul font-bold text-base text-[#1F2421]">Identitas & Data Kendaraan</h2>
                    <p class="text-xs text-stone-500 mt-0.5">
                        Pastikan nomor telepon aktif agar warga dapat menghubungi Anda saat penjemputan.
                    </p>
                </div>

                <form method="POST" action="{{ route('kurir.profil.update') }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    {{-- Data Diri Kurir --}}
                    <div class="space-y-4">
                        <div class="text-xs font-bold uppercase text-stone-400 tracking-wider">
                            1. Identitas Kurir
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="nama" class="block text-xs font-semibold text-stone-700 mb-1">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="nama" name="nama" required maxlength="100"
                                       value="{{ old('nama', $user->nama) }}"
                                       class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm text-[#1F2421] focus:border-[#175E54] focus:ring-1 focus:ring-[#175E54]">
                            </div>

                            <div>
                                <label for="no_hp" class="block text-xs font-semibold text-stone-700 mb-1">
                                    Nomor Handphone / WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="no_hp" name="no_hp" required maxlength="20"
                                       value="{{ old('no_hp', $user->no_hp) }}"
                                       class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm text-[#1F2421] focus:border-[#175E54] focus:ring-1 focus:ring-[#175E54]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-stone-700 mb-1">
                                Alamat Email Akun
                            </label>
                            <input type="email" disabled value="{{ $user->email }}"
                                   class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-stone-100 text-sm text-stone-500 cursor-not-allowed">
                            <span class="text-[11px] text-stone-500">Email terdaftar tidak dapat diubah secara mandiri.</span>
                        </div>
                    </div>

                    {{-- Data Kendaraan Operasional --}}
                    <div class="space-y-4 pt-4 border-t border-stone-200">
                        <div class="text-xs font-bold uppercase text-stone-400 tracking-wider">
                            2. Data Kendaraan Angkut Sampah
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="jenis_kendaraan" class="block text-xs font-semibold text-stone-700 mb-1">
                                    Jenis Kendaraan <span class="text-red-500">*</span>
                                </label>
                                <select id="jenis_kendaraan" name="jenis_kendaraan" required
                                        class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm text-[#1F2421] focus:border-[#175E54] focus:ring-1 focus:ring-[#175E54] bg-white">
                                    @php
                                        $pilihanKendaraan = [
                                            'Motor roda tiga',
                                            'Motor roda dua (keranjang)',
                                            'Mobil pikap',
                                            'Truk mini sampah',
                                            'Sepeda gerobak sampah',
                                        ];
                                    @endphp
                                    @foreach ($pilihanKendaraan as $pk)
                                        <option value="{{ $pk }}" {{ old('jenis_kendaraan', $profil->jenis_kendaraan) === $pk ? 'selected' : '' }}>
                                            {{ $pk }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="plat_nomor" class="block text-xs font-semibold text-stone-700 mb-1">
                                    Plat Nomor Polisi <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="plat_nomor" name="plat_nomor" required maxlength="15"
                                       value="{{ old('plat_nomor', $profil->plat_nomor) }}"
                                       placeholder="Mis: AG 4512 KR"
                                       class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm font-semibold uppercase text-[#1F2421] focus:border-[#175E54] focus:ring-1 focus:ring-[#175E54]">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="no_sim" class="block text-xs font-semibold text-stone-700 mb-1">
                                    Nomor Surat Izin Mengemudi (SIM)
                                </label>
                                <input type="text" id="no_sim" name="no_sim" maxlength="30"
                                       value="{{ old('no_sim', $profil->no_sim) }}"
                                       placeholder="Mis: 1234-5678-000001"
                                       class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm text-[#1F2421] focus:border-[#175E54] focus:ring-1 focus:ring-[#175E54]">
                            </div>

                            <div>
                                <label for="kapasitas_angkut_kg" class="block text-xs font-semibold text-stone-700 mb-1">
                                    Kapasitas Daya Angkut Maksimum (kg) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" id="kapasitas_angkut_kg" name="kapasitas_angkut_kg" step="1" min="10" max="5000" required
                                           value="{{ old('kapasitas_angkut_kg', (int) $profil->kapasitas_angkut_kg) }}"
                                           class="w-full px-3 py-2 pr-12 rounded-lg border border-stone-300 text-sm font-semibold text-[#1F2421] focus:border-[#175E54] focus:ring-1 focus:ring-[#175E54]">
                                    <span class="absolute right-3 top-2.5 text-xs font-bold text-stone-400">kg</span>
                                </div>
                                <span class="text-[11px] text-stone-500">Batas berat maksimum sampah dalam satu kali jalan.</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-stone-200 flex justify-end">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-[#175E54] hover:bg-[#0F3B36] text-white font-semibold text-sm cursor-pointer shadow-sm transition">
                            Simpan Perubahan Data
                        </button>
                    </div>
                </form>
            </section>
        </div>

        {{-- KOLOM KANAN (1 Kolom): KARTU INFORMASI AKUN & PANDUAN KURIR --}}
        <div class="space-y-6">
            {{-- Kartu Ringkasan Akun --}}
            <section class="bg-white border border-stone-200 rounded-xl p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-[#175E54] text-white font-bold text-lg flex items-center justify-center">
                        {{ strtoupper(substr($user->nama, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-[#1F2421] text-sm">{{ $user->nama }}</div>
                        <div class="text-xs text-stone-500">Kurir Pengangkut Sampah</div>
                    </div>
                </div>

                <div class="pt-3 border-t border-stone-100 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Status Akun:</span>
                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase {{ $user->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $user->status }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Status Ketersediaan:</span>
                        <span class="font-semibold text-[#1F2421] uppercase">{{ $profil->status_ketersediaan }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Rating Rata-rata:</span>
                        <span class="font-bold text-amber-500">⭐ {{ number_format($profil->rating_rata > 0 ? $profil->rating_rata : 5.0, 1) }} / 5.0</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Terakhir Update Lokasi:</span>
                        <span class="text-stone-600 angka-tabular">{{ $profil->lokasi_update_at?->diffForHumans() ?? 'Belum ada' }}</span>
                    </div>
                </div>
            </section>

            {{-- Kartu Panduan Operasional Kurir Ramah Lingkungan --}}
            <section class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-5 space-y-3 text-xs text-emerald-950">
                <div class="flex items-center gap-2 font-bold text-sm text-emerald-900">
                    <svg class="w-5 h-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Standar Operasional Kurir</span>
                </div>

                <ul class="space-y-2 list-disc list-inside text-stone-700">
                    <li>Gunakan perlengkapan keselamatan kerja (helm, sarung tangan tebal, dan masker).</li>
                    <li>Pastikan sampah yang diangkut tertutup terpal agar tidak berceceran di jalan raya.</li>
                    <li>Periksa kembali pemilahan sampah warga sebelum dimuat ke atas kendaraan.</li>
                    <li>Segera serahkan muatan ke pos timbang TPS agar warga segera menerima poin setoran.</li>
                </ul>
            </section>
        </div>
    </div>

@endsection

