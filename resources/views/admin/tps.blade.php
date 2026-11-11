@extends('layouts.admin')

@section('title', 'Fasilitas & Izin Limbah Medis/B3')
@section('heading', 'Fasilitas Penampungan & Verifikasi Izin Medis/B3')
@section('subheading', 'Verifikasi izin operasional limbah medis (izin_limbah_medis), pantau kuota penyimpanan (kg), dan tambahkan fasilitas baru.')

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Daftar TPS & Kapasitas (2 Kolom) --}}
        <section class="lg:col-span-2 space-y-4">
            @forelse ($daftarTps as $tps)
                @php
                    $persen = $tps->kapasitas_total_kg > 0
                        ? min(100, round(($tps->kapasitas_terpakai_kg / $tps->kapasitas_total_kg) * 100, 1))
                        : 0;
                    $sisaKg = max(0, $tps->kapasitas_total_kg - $tps->kapasitas_terpakai_kg);
                    $warnaBar = $persen >= 90 ? 'bg-red-600' : ($persen >= 70 ? 'bg-amber-500' : 'bg-[#1D6B45]');
                @endphp

                <article class="bg-white border border-stone-200 rounded-xl p-5 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base font-bold text-[#1F2421]">{{ $tps->nama_tps }}</h2>
                                @if ($tps->is_verified)
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-emerald-100 text-emerald-900">Terverifikasi</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-amber-100 text-amber-900">Menunggu Verifikasi</span>
                                @endif

                                @if ($tps->izin_limbah_medis)
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded bg-red-100 text-red-900">Berizin Limbah Medis</span>
                                @endif
                            </div>
                            <p class="text-xs text-stone-600 mt-1">{{ $tps->alamat }}</p>
                            <p class="text-xs text-stone-500 mt-0.5 angka-tabular">
                                Koordinat: {{ $tps->latitude }}, {{ $tps->longitude }}
                                · Jam: {{ $tps->jam_buka ? substr($tps->jam_buka, 0, 5) : '07:00' }}–{{ $tps->jam_tutup ? substr($tps->jam_tutup, 0, 5) : '17:00' }}
                                @if ($tps->no_izin)
                                    · No. Izin: <strong class="text-stone-700">{{ $tps->no_izin }}</strong>
                                @endif
                            </p>
                        </div>

                        <div class="text-right text-xs">
                            <div class="text-stone-500">Pengelola</div>
                            <div class="font-semibold text-[#1F2421]">{{ $tps->pengelola?->nama ?? '-' }}</div>
                            <div class="text-stone-500 angka-tabular">{{ $tps->pengelola?->no_hp }}</div>
                        </div>
                    </div>

                    {{-- Meter Kapasitas --}}
                    <div class="p-3.5 rounded-lg bg-stone-50 border border-stone-200 angka-tabular">
                        <div class="flex flex-wrap items-baseline justify-between gap-2 text-xs mb-1.5">
                            <span>
                                Terpakai: <strong class="text-[#1F2421]">{{ number_format($tps->kapasitas_terpakai_kg, 1, ',', '.') }} kg</strong>
                                dari {{ number_format($tps->kapasitas_total_kg, 0, ',', '.') }} kg
                            </span>
                            <span class="text-stone-600">
                                Sisa ruang: <strong>{{ number_format($sisaKg, 1, ',', '.') }} kg</strong> ({{ $persen }}%)
                            </span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-stone-200 overflow-hidden">
                            <div class="h-full {{ $warnaBar }}" style="width: {{ $persen }}%"></div>
                        </div>
                    </div>

                    {{-- Kontrol Verifikasi & Status TPS --}}
                    <form method="POST" action="{{ route('admin.tps.verifikasi', $tps) }}" class="pt-2 border-t border-stone-200 flex flex-wrap items-center justify-between gap-3">
                        @csrf
                        @method('PATCH')

                        <div class="flex flex-wrap items-center gap-4 text-xs">
                            <label class="inline-flex items-center gap-1.5 font-medium text-stone-700">
                                <span>Status:</span>
                                <select name="status" class="px-2.5 py-1 rounded border border-stone-300 bg-white font-semibold text-[#1F2421]">
                                    <option value="buka" @selected($tps->status === 'buka')>Buka</option>
                                    <option value="tutup" @selected($tps->status === 'tutup')>Tutup</option>
                                    <option value="penuh" @selected($tps->status === 'penuh')>Penuh</option>
                                </select>
                            </label>

                            <label class="inline-flex items-center gap-1.5 font-medium text-stone-700 cursor-pointer">
                                <input type="hidden" name="izin_limbah_medis" value="0">
                                <input type="checkbox" name="izin_limbah_medis" value="1" @checked($tps->izin_limbah_medis)
                                       class="w-4 h-4 rounded accent-[#1D6B45]">
                                <span>Izin limbah medis</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-2">
                            @if (! $tps->is_verified)
                                <input type="hidden" name="is_verified" value="1">
                                <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs font-semibold cursor-pointer">
                                    Setujui & Verifikasi TPS
                                </button>
                            @else
                                <input type="hidden" name="is_verified" value="1">
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-900 text-white text-xs font-semibold cursor-pointer">
                                    Simpan Perubahan
                                </button>
                            @endif
                        </div>
                    </form>
                </article>
            @empty
                <div class="bg-white border border-stone-200 rounded-xl p-6 text-sm text-stone-500">
                    Belum ada TPS yang terdaftar. Gunakan formulir di samping untuk menambahkan lokasi TPS pertama.
                </div>
            @endforelse
        </section>

        {{-- Form Tambah TPS Baru --}}
        <section class="bg-white border border-stone-200 rounded-xl p-5">
            <h2 class="text-base font-bold text-[#1F2421]">Tambah Lokasi TPS</h2>
            <p class="text-xs text-stone-500 mt-0.5 mb-4">TPS yang ditambahkan admin langsung berstatus terverifikasi.</p>

            <form method="POST" action="{{ route('admin.tps.store') }}" class="space-y-3.5 text-sm">
                @csrf

                <div>
                    <label for="nama_tps" class="block text-xs font-semibold text-stone-700 mb-1">Nama TPS</label>
                    <input id="nama_tps" name="nama_tps" type="text" value="{{ old('nama_tps') }}" required
                           placeholder="TPS Kenanga Bandar Lor"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                </div>

                <div>
                    <label for="pengelola_id" class="block text-xs font-semibold text-stone-700 mb-1">Akun Pengelola TPS</label>
                    <select id="pengelola_id" name="pengelola_id" required
                            class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                        <option value="">Pilih pengelola aktif…</option>
                        @foreach ($pengelolaTps as $p)
                            <option value="{{ $p->user_id }}" @selected(old('pengelola_id') == $p->user_id)>
                                {{ $p->nama }} ({{ $p->no_hp }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="alamat" class="block text-xs font-semibold text-stone-700 mb-1">Alamat Lengkap</label>
                    <input id="alamat" name="alamat" type="text" value="{{ old('alamat') }}" required
                           placeholder="Jl. Kenanga No. 45, Kota Kediri"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="latitude" class="block text-xs font-semibold text-stone-700 mb-1">Latitude</label>
                        <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', '-7.8166') }}" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm angka-tabular">
                    </div>
                    <div>
                        <label for="longitude" class="block text-xs font-semibold text-stone-700 mb-1">Longitude</label>
                        <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', '112.0114') }}" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm angka-tabular">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="kapasitas_total_kg" class="block text-xs font-semibold text-stone-700 mb-1">Kapasitas Total (kg)</label>
                        <input id="kapasitas_total_kg" name="kapasitas_total_kg" type="number" min="10" step="10" value="{{ old('kapasitas_total_kg', '2500') }}" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm angka-tabular">
                    </div>
                    <div>
                        <label for="no_izin" class="block text-xs font-semibold text-stone-700 mb-1">Nomor Izin (Opsional)</label>
                        <input id="no_izin" name="no_izin" type="text" value="{{ old('no_izin') }}" placeholder="IZN-TPS/2026/01"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="jam_buka" class="block text-xs font-semibold text-stone-700 mb-1">Jam Buka</label>
                        <input id="jam_buka" name="jam_buka" type="time" value="{{ old('jam_buka', '07:00') }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm angka-tabular">
                    </div>
                    <div>
                        <label for="jam_tutup" class="block text-xs font-semibold text-stone-700 mb-1">Jam Tutup</label>
                        <input id="jam_tutup" name="jam_tutup" type="time" value="{{ old('jam_tutup', '17:00') }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm angka-tabular">
                    </div>
                </div>

                <label class="flex items-center gap-2 pt-1 text-xs font-medium text-stone-700 cursor-pointer">
                    <input type="checkbox" name="izin_limbah_medis" value="1" @checked(old('izin_limbah_medis'))
                           class="w-4 h-4 rounded accent-[#1D6B45]">
                    <span>TPS ini memiliki izin resmi pengolahan limbah medis</span>
                </label>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white font-semibold text-sm cursor-pointer">
                    Simpan & Verifikasi TPS
                </button>
            </form>
        </section>
    </div>

@endsection

