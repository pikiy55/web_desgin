@extends('layouts.kurir')

@section('title', 'Manifes & Tugas Penjemputan Medis/B3')
@section('heading', 'Pos Kurir Pengangkutan Limbah Medis & B3')
@section('subheading', 'Ambil manifes penjemputan, pastikan kemasan Safety Box / Biohazard tersegel aman, dan serahkan ke fasilitas berizin.')

@section('content')

    {{-- Peringatan Akun Pending --}}
    @if (auth()->user()->status !== 'aktif')
        <div role="alert" class="p-4 rounded-xl bg-amber-50 border border-amber-300 text-amber-950 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <strong class="font-bold">Menunggu Verifikasi Administrator:</strong>
                <p class="mt-0.5 text-xs text-amber-900">
                    Akun kurir Anda saat ini berstatus <strong>{{ strtoupper(auth()->user()->status) }}</strong>. Anda belum dapat mengambil antrean tugas penjemputan hingga akun Anda disetujui oleh Administrator.
                </p>
            </div>
        </div>
    @endif

    {{-- Pita Ringkasan Statistik Kurir --}}
    <section aria-label="Ringkasan Kinerja Kurir" class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-stone-200">
            <div class="p-4">
                <div class="text-xs text-stone-500">Tugas Aktif Berjalan</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold angka-tabular {{ $ringkasan['tugas_aktif'] > 0 ? 'text-[#175E54]' : 'text-[#1F2421]' }}">
                        {{ $ringkasan['tugas_aktif'] }}
                    </span>
                    <span class="text-xs text-stone-500">{{ $ringkasan['tugas_aktif'] > 0 ? 'sedang diantar' : 'tidak ada' }}</span>
                </div>
            </div>

            <div class="p-4">
                <div class="text-xs text-stone-500">Antrean Tersedia</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold angka-tabular text-amber-700">{{ $ringkasan['antrean_tersedia'] }}</span>
                    <span class="text-xs text-stone-500">menunggu kurir</span>
                </div>
            </div>

            <div class="p-4">
                <div class="text-xs text-stone-500">Diantar Hari Ini</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold angka-tabular text-[#1F2421]">
                        {{ number_format($ringkasan['berat_hari_ini_kg'], 1, ',', '.') }}
                    </span>
                    <span class="text-xs text-stone-500">kg ({{ $ringkasan['selesai_hari_ini'] }} setoran)</span>
                </div>
            </div>

            <div class="p-4">
                <div class="text-xs text-stone-500">Total Selesai (Semua)</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold angka-tabular text-[#1F2421]">
                        {{ $ringkasan['total_selesai'] }}
                    </span>
                    <span class="text-xs text-stone-500">pengantaran</span>
                </div>
            </div>

            <div class="p-4">
                <div class="text-xs text-stone-500">Kepuasan & Rating</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold angka-tabular text-amber-500">
                        ⭐ {{ number_format($ringkasan['rating_rata'] > 0 ? $ringkasan['rating_rata'] : 5.0, 1) }}
                    </span>
                    <span class="text-xs text-stone-500">/ 5.0</span>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- KOLOM UTAMA (2 Kolom): TUGAS AKTIF & DAFTAR ANTREAN JEMPUT --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. KARTU TUGAS AKTIF (JIKA ADA) --}}
            @if ($tugasAktif)
                @php
                    $setoran = $tugasAktif->setoran;
                    $pengguna = $setoran->pengguna;
                    $alamat = $tugasAktif->alamat;
                    $tps = $setoran->tps;
                    $totalEstKg = $setoran->detail->sum('berat_estimasi_kg');

                    // Link Maps
                    $mapsAlamat = $alamat && $alamat->latitude && $alamat->longitude
                        ? "https://www.google.com/maps/search/?api=1&query={$alamat->latitude},{$alamat->longitude}"
                        : ($alamat ? "https://www.google.com/maps/search/?api=1&query=" . urlencode($alamat->alamat_lengkap) : '#');

                    $mapsTps = $tps && $tps->latitude && $tps->longitude
                        ? "https://www.google.com/maps/search/?api=1&query={$tps->latitude},{$tps->longitude}"
                        : ($tps ? "https://www.google.com/maps/search/?api=1&query=" . urlencode($tps->alamat) : '#');

                    // Link WhatsApp
                    $cleanPhone = preg_replace('/[^0-9]/', '', $pengguna?->no_hp ?? '');
                    if (str_starts_with($cleanPhone, '0')) {
                        $cleanPhone = '62' . substr($cleanPhone, 1);
                    }
                    $waLink = $cleanPhone ? "https://wa.me/{$cleanPhone}?text=" . urlencode("Halo {$pengguna->nama}, saya kurir dari Setoran Sampah sedang menuju lokasi Anda untuk penjemputan setoran {$setoran->kode_setoran}.") : '#';
                @endphp

                <section aria-labelledby="judul-tugas-aktif" class="bg-white border-2 border-[#175E54] rounded-xl overflow-hidden shadow-sm">
                    {{-- Kepala Tugas Aktif --}}
                    <div class="px-5 py-4 bg-[#0F3B36] text-white flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-3 h-3 rounded-full bg-[#F2C230] animate-ping"></span>
                            <h2 id="judul-tugas-aktif" class="font-judul font-bold text-base text-white">
                                Tugas Aktif: {{ $setoran->kode_setoran }}
                            </h2>
                            <span class="px-2.5 py-0.5 text-xs font-bold uppercase rounded bg-[#F2C230] text-[#1F2421]">
                                {{ $tugasAktif->status === 'diterima' ? 'Menuju Lokasi Warga' : 'Mengangkut Menuju TPS' }}
                            </span>
                        </div>

                        <div class="text-xs text-teal-200 angka-tabular">
                            Diterima pukul: <strong>{{ $tugasAktif->diterima_at?->format('H:i') ?? '-' }} WIB</strong>
                        </div>
                    </div>

                    {{-- Indikator Visual Alur Progres Penjemputan --}}
                    <div class="px-6 py-3.5 bg-stone-50 border-b border-stone-200">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <div class="flex items-center gap-2 text-[#175E54]">
                                <span class="w-6 h-6 rounded-full bg-[#175E54] text-white flex items-center justify-center text-xs font-bold">1</span>
                                <span>Tugas Diterima</span>
                            </div>
                            <div class="flex-1 h-1 mx-3 rounded {{ $tugasAktif->status === 'diangkut' ? 'bg-[#175E54]' : 'bg-stone-300' }}"></div>
                            <div class="flex items-center gap-2 {{ $tugasAktif->status === 'diangkut' ? 'text-[#175E54]' : 'text-stone-400' }}">
                                <span class="w-6 h-6 rounded-full {{ $tugasAktif->status === 'diangkut' ? 'bg-[#175E54] text-white' : 'bg-stone-200 text-stone-500' }} flex items-center justify-center text-xs font-bold">2</span>
                                <span>Sampah Dimuat</span>
                            </div>
                            <div class="flex-1 h-1 mx-3 rounded bg-stone-300"></div>
                            <div class="flex items-center gap-2 text-stone-400">
                                <span class="w-6 h-6 rounded-full bg-stone-200 text-stone-500 flex items-center justify-center text-xs font-bold">3</span>
                                <span>Tiba di TPS</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 space-y-5">
                        {{-- Grid Titik Jemput & Titik Antar --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Titik Jemput (Warga) --}}
                            <div class="p-4 rounded-xl border border-stone-200 bg-stone-50 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Titik Jemput (Warga)
                                    </span>
                                    @if ($cleanPhone)
                                        <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-100 hover:bg-emerald-200 px-2 py-1 rounded">
                                            <span>Chat WA</span>
                                        </a>
                                    @endif
                                </div>

                                <div class="font-bold text-[#1F2421] text-sm">{{ $pengguna?->nama ?? 'Warga Penyetor' }}</div>
                                <div class="text-xs text-stone-600 angka-tabular">
                                    Telp: <a href="tel:{{ $pengguna?->no_hp }}" class="font-semibold text-[#175E54] hover:underline">{{ $pengguna?->no_hp ?? '-' }}</a>
                                </div>
                                <p class="text-xs text-stone-700 bg-white p-2 rounded border border-stone-200">
                                    {{ $alamat?->alamat_lengkap ?? 'Alamat penjemputan warga' }}
                                </p>

                                <a href="{{ $mapsAlamat }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#175E54] hover:underline pt-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Buka Petunjuk Arah ke Rumah Warga (Google Maps)
                                </a>
                            </div>

                            {{-- Titik Antar (TPS Tujuan) --}}
                            <div class="p-4 rounded-xl border border-stone-200 bg-stone-50 space-y-2">
                                <div class="text-xs font-bold uppercase tracking-wider text-stone-500 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Titik Antar (TPS Tujuan)
                                </div>

                                <div class="font-bold text-[#1F2421] text-sm">{{ $tps?->nama_tps ?? 'TPS Tujuan' }}</div>
                                <div class="text-xs text-stone-600">
                                    Operasional: <span class="font-semibold text-stone-800">{{ $tps?->jam_buka }} - {{ $tps?->jam_tutup }} WIB</span>
                                </div>
                                <p class="text-xs text-stone-700 bg-white p-2 rounded border border-stone-200">
                                    {{ $tps?->alamat ?? 'Alamat TPS' }}
                                </p>

                                <a href="{{ $mapsTps }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#175E54] hover:underline pt-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Buka Petunjuk Arah ke TPS (Google Maps)
                                </a>
                            </div>
                        </div>

                        {{-- Catatan dari Warga & Rincian Muatan --}}
                        <div class="border border-stone-200 rounded-lg p-3.5 bg-stone-50/50 space-y-3">
                            @if ($setoran->catatan_pengguna)
                                <div class="text-xs text-stone-700">
                                    <span class="font-bold text-[#1F2421]">Pesan/Patokan Warga:</span> "{{ $setoran->catatan_pengguna }}"
                                </div>
                            @endif

                            <div>
                                <div class="text-xs font-bold text-stone-700 mb-2 flex items-center justify-between">
                                    <span>Rincian Item Limbah yang Dijemput:</span>
                                    <span class="text-stone-500 angka-tabular">Estimasi Total: <strong>{{ number_format($totalEstKg, 0, ',', '.') }} item</strong></span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($setoran->detail as $item)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-white border border-stone-300 text-xs font-medium text-stone-800">
                                            <span class="font-semibold">{{ $item->jenisSampah?->nama ?? 'Sampah' }}</span>
                                            <span class="text-stone-500 uppercase text-[10px]">({{ $item->jenisSampah?->kategori }})</span>
                                            <span class="font-bold text-[#175E54] angka-tabular">{{ number_format($item->berat_estimasi_kg, 0, ',', '.') }} item</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- FORMULIR AKSI PROSES TUGAS KURIR --}}
                        <div class="pt-3 border-t border-stone-200">
                            @if ($tugasAktif->status === 'diterima')
                                {{-- Form Konfirmasi Dimuat (Menjadi 'diangkut') --}}
                                <form method="POST" action="{{ route('kurir.tugas.angkut', $tugasAktif) }}" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    @method('PATCH')

                                    <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-4 space-y-3">
                                        <div class="flex items-center gap-2 text-emerald-900 font-bold text-sm">
                                            <svg class="w-5 h-5 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Langkah 1: Konfirmasi Sampah Sudah Dimuat ke Kendaraan</span>
                                        </div>
                                        <p class="text-xs text-stone-600">
                                            Tekan tombol di bawah setelah Anda tiba di rumah warga dan menaikkan seluruh kantong/karung sampah ke kendaraan Anda.
                                        </p>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            <div>
                                                <label class="block font-medium text-stone-700 mb-1">
                                                    Foto Bukti Pengambilan (Opsional):
                                                </label>
                                                <input type="file" name="foto_bukti_ambil" accept="image/*"
                                                       class="block w-full text-xs text-stone-600 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                                            </div>

                                            <div>
                                                <label class="block font-medium text-stone-700 mb-1">
                                                    Catatan Kondisi Sampah (Opsional):
                                                </label>
                                                <input type="text" name="catatan_kurir" maxlength="255"
                                                       placeholder="Contoh: 3 karung terpilah kondisi rapi dan kering"
                                                       class="w-full px-3 py-1.5 rounded-lg border border-stone-300 bg-white text-xs text-stone-800">
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                            <button type="submit"
                                                    class="px-5 py-2.5 rounded-lg bg-[#175E54] hover:bg-[#0F3B36] text-white font-semibold text-sm cursor-pointer shadow-sm transition">
                                                Konfirmasi Sampah Telah Dimuat & Lanjut ke TPS
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                {{-- Opsi Batal Tugas Jika Ada Kendala --}}
                                <div class="mt-3 flex justify-end">
                                    <details class="text-xs">
                                        <summary class="text-red-700 hover:text-red-800 font-semibold cursor-pointer">
                                            Batalkan tugas ini kembali ke antrean umum…
                                        </summary>
                                        <form method="POST" action="{{ route('kurir.tugas.batal', $tugasAktif) }}" class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <p class="text-red-900 font-medium text-[11px]">
                                                Tugas akan dilepas kembali ke antrean agar dapat diambil oleh rekan kurir lainnya.
                                            </p>
                                            <input type="text" name="alasan" required maxlength="255"
                                                   placeholder="Alasan pembatalan (mis: ban bocor / kendala teknis)"
                                                   class="w-full px-2.5 py-1.5 text-xs rounded border border-red-300 bg-white">
                                            <button type="submit" class="px-3 py-1 rounded bg-red-700 hover:bg-red-800 text-white font-semibold text-xs cursor-pointer">
                                                Konfirmasi Lepas Tugas
                                            </button>
                                        </form>
                                    </details>
                                </div>

                            @elseif ($tugasAktif->status === 'diangkut')
                                {{-- Form Konfirmasi Tiba di TPS (Menjadi 'tiba_di_tps') --}}
                                <form method="POST" action="{{ route('kurir.tugas.tiba', $tugasAktif) }}" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    @method('PATCH')

                                    <div class="bg-blue-50/70 border border-blue-200 rounded-xl p-4 space-y-3">
                                        <div class="flex items-center gap-2 text-blue-900 font-bold text-sm">
                                            <svg class="w-5 h-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <span>Langkah 2: Konfirmasi Tiba di Pos TPS Tujuan</span>
                                        </div>
                                        <p class="text-xs text-stone-600">
                                            Sampah sedang Anda bawa menuju <strong>{{ $tps?->nama_tps }}</strong>. Setelah tiba di pos penimbangan TPS, tekan tombol di bawah untuk menyerahkan setoran kepada petugas timbang.
                                        </p>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                            <div>
                                                <label class="block font-medium text-stone-700 mb-1">
                                                    Foto Bukti Penyerahan di TPS (Opsional):
                                                </label>
                                                <input type="file" name="foto_bukti_serah" accept="image/*"
                                                       class="block w-full text-xs text-stone-600 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                            </div>

                                            <div>
                                                <label class="block font-medium text-stone-700 mb-1">
                                                    Catatan Serah Terima (Opsional):
                                                </label>
                                                <input type="text" name="catatan_kurir" maxlength="255"
                                                       placeholder="Contoh: Diserahkan langsung ke petugas timbang pos"
                                                       class="w-full px-3 py-1.5 rounded-lg border border-stone-300 bg-white text-xs text-stone-800">
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                                            <button type="submit"
                                                    class="px-5 py-2.5 rounded-lg bg-blue-700 hover:bg-blue-800 text-white font-semibold text-sm cursor-pointer shadow-sm transition">
                                                Konfirmasi Tiba di TPS & Serahkan ke Petugas
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            {{-- 2. DAFTAR ANTREAN TUGAS BARU MENUNGGU DIJEMPUT --}}
            <section aria-labelledby="judul-antrean" class="space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 id="judul-antrean" class="text-base font-bold text-[#1F2421]">
                            Antrean Tugas Tersedia ({{ $antreanTugas->count() }})
                        </h2>
                        <p class="text-xs text-stone-500">
                            Pesanan penjemputan baru dari warga yang menunggu untuk diambil oleh kurir terdekat.
                        </p>
                    </div>

                    @if ($tugasAktif)
                        <span class="px-2.5 py-1 text-xs rounded bg-stone-100 text-stone-600 border border-stone-200">
                            Selesaikan tugas aktif di atas untuk mengambil pesanan baru
                        </span>
                    @endif
                </div>

                @forelse ($antreanTugas as $antrean)
                    @php
                        $setoranA = $antrean->setoran;
                        $penggunaA = $setoranA->pengguna;
                        $alamatA = $antrean->alamat;
                        $tpsA = $setoranA->tps;
                        $totalKgA = $setoranA->detail->sum('berat_estimasi_kg');

                        $bisaAmbil = ! $tugasAktif && auth()->user()->status === 'aktif';
                    @endphp

                    <article class="bg-white border border-stone-200 rounded-xl p-5 hover:border-stone-300 transition space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-stone-100">
                            <div class="flex items-center gap-2">
                                <span class="font-judul font-bold text-sm text-[#1F2421] angka-tabular">
                                    {{ $setoranA->kode_setoran }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-amber-100 text-amber-900 uppercase">
                                    Menunggu Kurir
                                </span>
                            </div>

                            <div class="text-xs text-stone-500 angka-tabular">
                                Dibuat: {{ $setoranA->created_at?->format('d/m/Y H:i') ?? '-' }} WIB
                            </div>
                        </div>

                        {{-- Rute Jemput -> TPS --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-lg bg-stone-50 border border-stone-200">
                                <div class="font-bold text-stone-500 mb-1 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    LOKASI JEMPUT (WARGA)
                                </div>
                                <div class="font-bold text-[#1F2421] text-sm">{{ $penggunaA?->nama ?? 'Warga' }}</div>
                                <p class="text-stone-600 mt-1 line-clamp-2">
                                    {{ $alamatA?->alamat_lengkap ?? 'Alamat warga' }}
                                </p>
                            </div>

                            <div class="p-3 rounded-lg bg-stone-50 border border-stone-200">
                                <div class="font-bold text-stone-500 mb-1 flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    TPS TUJUAN
                                </div>
                                <div class="font-bold text-[#1F2421] text-sm">{{ $tpsA?->nama_tps ?? 'TPS Tujuan' }}</div>
                                <p class="text-stone-600 mt-1 line-clamp-2">
                                    {{ $tpsA?->alamat ?? 'Alamat TPS' }}
                                </p>
                            </div>
                        </div>

                        {{-- Detail Sampah & Catatan --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 text-xs pt-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-stone-600">Muatan:</span>
                                <span class="px-2 py-1 rounded bg-stone-100 font-bold text-[#1F2421] angka-tabular">
                                    Est. {{ number_format($totalKgA, 0, ',', '.') }} item
                                </span>

                                <span class="text-stone-400">·</span>

                                <span class="text-stone-600">
                                    {{ $setoranA->detail->map(fn($d) => $d->jenisSampah?->nama)->filter()->join(', ') }}
                                </span>
                            </div>

                            {{-- Tombol Ambil Tugas --}}
                            <form method="POST" action="{{ route('kurir.tugas.terima', $antrean) }}">
                                @csrf
                                <button type="submit"
                                        {{ ! $bisaAmbil ? 'disabled' : '' }}
                                        class="px-4 py-2 rounded-lg font-semibold text-xs transition cursor-pointer {{ $bisaAmbil ? 'bg-[#175E54] hover:bg-[#0F3B36] text-white shadow-sm' : 'bg-stone-200 text-stone-400 cursor-not-allowed' }}">
                                    Ambil Tugas Ini
                                </button>
                            </form>
                        </div>

                        @if ($setoranA->catatan_pengguna)
                            <div class="text-xs text-stone-500 italic bg-stone-50 p-2 rounded">
                                Catatan warga: "{{ $setoranA->catatan_pengguna }}"
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="bg-white border border-stone-200 rounded-xl p-8 text-center space-y-2">
                        <svg class="w-10 h-10 text-stone-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm font-semibold text-[#1F2421]">Tidak ada antrean tugas baru saat ini</div>
                        <p class="text-xs text-stone-500 max-w-sm mx-auto">
                            Semua permintaan penjemputan sampah warga telah terlayani atau belum ada setoran baru dengan metode jemput.
                        </p>
                    </div>
                @endforelse
            </section>
        </div>

        {{-- KOLOM SAMPING (1 Kolom): PROFIL KENDARAAN, NOTIFIKASI, RIWAYAT SINGKAT --}}
        <div class="space-y-6">

            {{-- 1. KARTU STATUS KENDARAAN & PROFIL --}}
            <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3.5 border-b border-stone-200 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#1F2421]">Armada & Kendaraan</h2>
                    <a href="{{ route('kurir.profil') }}" class="text-xs font-semibold text-[#175E54] hover:underline">
                        Edit Data
                    </a>
                </div>

                <div class="p-4 space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Nama Kurir:</span>
                        <span class="font-semibold text-[#1F2421]">{{ auth()->user()->nama }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Kendaraan:</span>
                        <span class="font-semibold text-[#1F2421]">{{ $profil->jenis_kendaraan }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Plat Nomor:</span>
                        <span class="font-bold text-[#1F2421] px-2 py-0.5 rounded bg-stone-100 border border-stone-300">
                            {{ $profil->plat_nomor }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Daya Angkut Maks:</span>
                        <span class="font-semibold text-[#1F2421] angka-tabular">
                            {{ number_format($profil->kapasitas_angkut_kg, 0, ',', '.') }} kg
                        </span>
                    </div>

                    @if ($ringkasan['beban_aktif_kg'] > 0)
                        <div class="pt-2 border-t border-stone-200 space-y-1">
                            @php
                                $persenBeban = $profil->kapasitas_angkut_kg > 0
                                    ? min(100, round(($ringkasan['beban_aktif_kg'] / $profil->kapasitas_angkut_kg) * 100))
                                    : 0;
                            @endphp
                            <div class="flex items-center justify-between text-stone-600">
                                <span>Muatan Aktif Saat Ini:</span>
                                <span class="font-bold text-[#175E54] angka-tabular">{{ number_format($ringkasan['beban_aktif_kg'], 1, ',', '.') }} kg</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-stone-200 overflow-hidden">
                                <div class="h-full bg-[#175E54]" style="width: {{ $persenBeban }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            {{-- 2. NOTIFIKASI KURIR --}}
            <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3.5 border-b border-stone-200 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#1F2421]">Pemberitahuan Tugas</h2>
                    @if ($notifikasiBelumBaca->isNotEmpty())
                        <form method="POST" action="{{ route('kurir.notifikasi.baca') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-[#175E54] hover:underline cursor-pointer">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                </div>

                @if ($notifikasiBelumBaca->isEmpty())
                    <div class="p-4 text-xs text-stone-500">
                        Belum ada pemberitahuan baru.
                    </div>
                @else
                    <ul class="divide-y divide-stone-200 text-xs">
                        @foreach ($notifikasiBelumBaca as $notif)
                            <li class="p-3.5 space-y-1 hover:bg-stone-50 transition">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-[#1F2421]">{{ $notif->judul }}</span>
                                    <span class="text-stone-400 angka-tabular">{{ $notif->created_at?->format('H:i') }}</span>
                                </div>
                                <p class="text-stone-600">{{ $notif->pesan }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- 3. PENGANTARAN TERAKHIR (RINGKASAN) --}}
            <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3.5 border-b border-stone-200 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#1F2421]">Pengantaran Terakhir</h2>
                    <a href="{{ route('kurir.riwayat') }}" class="text-xs font-semibold text-[#175E54] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                @if ($riwayatSingkat->isEmpty())
                    <div class="p-4 text-xs text-stone-500">
                        Belum ada riwayat pengantaran yang diselesaikan.
                    </div>
                @else
                    <ul class="divide-y divide-stone-100 text-xs">
                        @foreach ($riwayatSingkat as $itemR)
                            @php
                                $sR = $itemR->setoran;
                            @endphp
                            <li class="p-3.5 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[#1F2421] angka-tabular">{{ $sR->kode_setoran }}</span>
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $sR->status === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $sR->status === 'selesai' ? 'Selesai Ditimbang' : 'Tiba di TPS' }}
                                    </span>
                                </div>
                                <div class="text-stone-600 flex items-center justify-between">
                                    <span>Tujuan: {{ $sR->tps?->nama_tps }}</span>
                                    <span class="angka-tabular">{{ $itemR->tiba_at?->format('d/m H:i') }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>

@endsection

