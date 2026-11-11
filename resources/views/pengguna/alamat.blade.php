@extends('layouts.pengguna')

@section('title', 'Alamat Jemput & Profil')
@section('heading', 'Alamat Titik Penjemputan & Profil Penghasil')
@section('subheading', 'Kelola titik koordinat penjemputan kurir (Rumah, Klinik, Apotek) dan informasi kontak Anda.')

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- KOLOM KIRI (7 Kolom): DAFTAR ALAMAT & FORM TAMBAH ALAMAT --}}
        <div class="lg:col-span-7 space-y-6">

            {{-- Daftar Alamat Terdaftar --}}
            <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-stone-200 flex items-center justify-between">
                    <div>
                        <h2 class="font-judul font-bold text-base text-[#1F2421]">Daftar Alamat Titik Penjemputan ({{ $daftarAlamat->count() }})</h2>
                        <p class="text-xs text-stone-500">Digunakan kurir saat menjemput kemasan limbah medis atau B3.</p>
                    </div>
                </div>

                @if ($daftarAlamat->isEmpty())
                    <div class="p-6 text-xs text-stone-500">
                        Belum ada alamat penjemputan yang disimpan. Tambahkan alamat menggunakan formulir di bawah.
                    </div>
                @else
                    <div class="divide-y divide-stone-200">
                        @foreach ($daftarAlamat as $al)
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-[#1F2421]">{{ $al->label }}</span>
                                        @if ($al->is_default)
                                            <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-900 font-bold text-[10px] uppercase">
                                                Titik Jemput Utama
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-stone-600">{{ $al->alamat_lengkap }}</p>
                                    <p class="text-[11px] text-stone-400 angka-tabular">
                                        Koordinat: {{ $al->latitude }}, {{ $al->longitude }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    @if (! $al->is_default)
                                        <form method="POST" action="{{ route('pengguna.alamat.utama', $al) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-stone-300 text-stone-700 hover:bg-stone-100 text-xs font-semibold cursor-pointer">
                                                Jadikan Utama
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('pengguna.alamat.destroy', $al) }}" onsubmit="return confirm('Hapus alamat titik jemput ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 text-xs font-semibold cursor-pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Form Tambah Alamat Baru --}}
            <section class="bg-white border border-stone-200 rounded-xl p-5 space-y-4">
                <div>
                    <h2 class="font-judul font-bold text-base text-[#1F2421]">Tambah Alamat Penjemputan Baru</h2>
                    <p class="text-xs text-stone-500">Masukkan label lokasi (misal: Rumah, Klinik Pratama, Apotek) dan alamat lengkap.</p>
                </div>

                <form method="POST" action="{{ route('pengguna.alamat.store') }}" class="space-y-3.5 text-sm">
                    @csrf

                    <div>
                        <label for="label" class="block text-xs font-semibold text-stone-700 mb-1">Label Lokasi <span class="text-red-600">*</span></label>
                        <input id="label" name="label" type="text" required maxlength="50"
                               value="{{ old('label') }}" placeholder="Mis: Rumah / Klinik Pratama Sehat / Apotek"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                    </div>

                    <div>
                        <label for="alamat_lengkap" class="block text-xs font-semibold text-stone-700 mb-1">Alamat Lengkap &amp; Patokan <span class="text-red-600">*</span></label>
                        <input id="alamat_lengkap" name="alamat_lengkap" type="text" required maxlength="255"
                               value="{{ old('alamat_lengkap') }}" placeholder="Jl. Vetera No. 18, Kec. Mojoroto, Kota Kediri"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="latitude" class="block text-xs font-semibold text-stone-700 mb-1">Latitude</label>
                            <input id="latitude" name="latitude" type="number" step="0.0000001" required
                                   value="{{ old('latitude', '-7.8166000') }}"
                                   class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                        </div>
                        <div>
                            <label for="longitude" class="block text-xs font-semibold text-stone-700 mb-1">Longitude</label>
                            <input id="longitude" name="longitude" type="number" step="0.0000001" required
                                   value="{{ old('longitude', '112.0114000') }}"
                                   class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 text-xs font-medium text-stone-700 cursor-pointer">
                        <input type="checkbox" name="is_default" value="1" checked class="w-4 h-4 rounded accent-[#1D6B45]">
                        <span>Tetapkan sebagai alamat penjemputan utama</span>
                    </label>

                    <div>
                        <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs sm:text-sm font-semibold cursor-pointer">
                            Simpan Alamat Titik Jemput
                        </button>
                    </div>
                </form>
            </section>
        </div>

        {{-- KOLOM KANAN (5 Kolom): PROFIL & KEAMANAN AKUN PENGHASIL --}}
        <section class="lg:col-span-5 bg-white border border-stone-200 rounded-xl p-5 space-y-4">
            <div>
                <h2 class="font-judul font-bold text-base text-[#1F2421]">Profil &amp; Kontak Penghasil</h2>
                <p class="text-xs text-stone-500">Pastikan nomor WhatsApp aktif untuk koordinasi penjemputan kurir.</p>
            </div>

            <form method="POST" action="{{ route('pengguna.profil.update') }}" class="space-y-4 text-sm">
                @csrf
                @method('PATCH')

                <div>
                    <label for="nama" class="block text-xs font-semibold text-stone-700 mb-1">Nama Lengkap / Instansi <span class="text-red-600">*</span></label>
                    <input id="nama" name="nama" type="text" required maxlength="100"
                           value="{{ old('nama', $user->nama) }}"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                </div>

                <div>
                    <label for="no_hp" class="block text-xs font-semibold text-stone-700 mb-1">Nomor HP / WhatsApp <span class="text-red-600">*</span></label>
                    <input id="no_hp" name="no_hp" type="text" required maxlength="20"
                           value="{{ old('no_hp', $user->no_hp) }}"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">Email Akun</label>
                    <input type="email" disabled value="{{ $user->email }}"
                           class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-stone-100 text-stone-500 cursor-not-allowed">
                </div>

                <div class="pt-3 border-t border-stone-200 space-y-3">
                    <div class="text-xs font-bold uppercase text-stone-500">Ganti Kata Sandi (Opsional)</div>

                    <div>
                        <label for="password_baru" class="block text-xs font-semibold text-stone-700 mb-1">Kata Sandi Baru</label>
                        <input id="password_baru" name="password_baru" type="password" minlength="8"
                               placeholder="Kosongkan jika tidak ingin mengubah"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                    </div>

                    <div>
                        <label for="password_baru_confirmation" class="block text-xs font-semibold text-stone-700 mb-1">Ulangi Kata Sandi Baru</label>
                        <input id="password_baru_confirmation" name="password_baru_confirmation" type="password" minlength="8"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-stone-800 hover:bg-stone-900 text-white font-semibold text-xs sm:text-sm cursor-pointer">
                    Simpan Perubahan Profil
                </button>
            </form>
        </section>
    </div>

@endsection

