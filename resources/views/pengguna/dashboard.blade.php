@extends('layouts.pengguna')

@section('title', 'Ikhtisar & Manifes Aktif')
@section('heading', 'Halo, '.$user->nama)
@section('subheading', 'Kelola penyetoran limbah medis, B3, dan daur ulang Anda ke fasilitas penampungan berizin.')

@section('header_actions')
    <a href="{{ route('pengguna.setoran.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs sm:text-sm font-semibold shadow-2xs transition cursor-pointer">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Buat Manifes Setoran Baru</span>
    </a>
@endsection

@section('content')

    {{-- Pita Ringkasan Utama Penghasil / Pengguna --}}
    <section aria-label="Ringkasan Akun Pengguna" class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-stone-200">
            <div class="p-4">
                <div class="text-xs text-stone-500">Saldo Poin Insentif B3</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold angka-tabular text-[#1D6B45]">
                        {{ number_format($ringkasan['saldo_poin'], 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-semibold text-stone-500">poin</span>
                </div>
            </div>

            <div class="p-4">
                <div class="text-xs text-stone-500">Manifes Sedang Berjalan</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold angka-tabular {{ $ringkasan['manifes_aktif'] > 0 ? 'text-amber-700' : 'text-[#1F2421]' }}">
                        {{ $ringkasan['manifes_aktif'] }}
                    </span>
                    <span class="text-xs text-stone-500">dokumen</span>
                </div>
            </div>

            <div class="p-4">
                <div class="text-xs text-stone-500">Manifes Selesai Ditimbang</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold angka-tabular text-[#1F2421]">
                        {{ $ringkasan['manifes_selesai'] }}
                    </span>
                    <span class="text-xs text-stone-500">setoran</span>
                </div>
            </div>

            <div class="p-4">
                <div class="text-xs text-stone-500">Total Limbah Tersalurkan</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold angka-tabular text-[#1F2421]">
                        {{ number_format($ringkasan['total_berat_kg'], 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-semibold text-stone-500">item</span>
                </div>
            </div>

            <div class="p-4 col-span-2 sm:col-span-1">
                <div class="text-xs text-stone-500">Titik Jemput Terdaftar</div>
                <div class="mt-1 flex items-center justify-between">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold angka-tabular text-[#1F2421]">
                            {{ $ringkasan['jumlah_alamat'] }}
                        </span>
                        <span class="text-xs text-stone-500">alamat</span>
                    </div>
                    <a href="{{ route('pengguna.alamat') }}" class="text-xs font-semibold text-[#1D6B45] hover:underline">
                        Atur
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Panduan Singkat 4 Kode Warna Kemasan K3 --}}
    <section aria-label="Protokol Kemasan K3" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
        <div class="p-3.5 rounded-xl bg-[#FFFDF5] border border-amber-300 flex items-start gap-3">
            <span class="px-2 py-1 rounded bg-[#F2C230] text-[#1F2421] font-judul font-extrabold text-xs shrink-0">KUNING</span>
            <div class="text-xs">
                <div class="font-bold text-[#1F2421]">Medis Infeksius</div>
                <p class="text-stone-600 mt-0.5">Masker, sarung tangan, perban bekas pakai dalam kantong biohazard.</p>
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-[#FFFBFB] border border-[#F2B8B5] flex items-start gap-3">
            <span class="px-2 py-1 rounded bg-[#B3261E] text-white font-judul font-extrabold text-xs shrink-0">SHARPS</span>
            <div class="text-xs">
                <div class="font-bold text-[#1F2421]">Medis Benda Tajam</div>
                <p class="text-stone-600 mt-0.5">Jarum suntik, lancet, ampul wajib di dalam <em>Safety Box</em> tahan tusuk.</p>
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-stone-50 border border-stone-300 flex items-start gap-3">
            <span class="px-2 py-1 rounded bg-amber-800 text-white font-judul font-extrabold text-xs shrink-0">COKELAT</span>
            <div class="text-xs">
                <div class="font-bold text-[#1F2421]">Medis Farmasi</div>
                <p class="text-stone-600 mt-0.5">Obat kedaluwarsa, sirup tertutup, dan kemasan sisa farmasi.</p>
            </div>
        </div>

        <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-200 flex items-start gap-3">
            <span class="px-2 py-1 rounded bg-[#1D6B45] text-white font-judul font-extrabold text-xs shrink-0">B3 + POIN</span>
            <div class="text-xs">
                <div class="font-bold text-[#1F2421]">B3, Elektronik &amp; Daur Ulang</div>
                <p class="text-stone-600 mt-0.5">Baterai, lampu neon, elektronik, plastik/logam (mendapat poin/kg).</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- KOLOM KIRI (2 Kolom): MANIFES AKTIF BERJALAN & RIWAYAT TERAKHIR --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. MANIFES SETORAN AKTIF BERJALAN --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#1F2421]">Manifes Setoran Sedang Berjalan ({{ $setoranAktif->count() }})</h2>
                        <p class="text-xs text-stone-500">Pantau posisi kurir penjemputan atau status antrean timbang di fasilitas tujuan.</p>
                    </div>
                    <a href="{{ route('pengguna.setoran.create') }}" class="text-xs font-semibold text-[#1D6B45] hover:underline">
                        + Buat Manifes Baru
                    </a>
                </div>

                @forelse ($setoranAktif as $s)
                    @php
                        $adaMedis = $s->detail->contains(fn ($d) => $d->jenisSampah?->kategori === 'medis');
                        $totalEstKg = $s->detail->sum('berat_estimasi_kg');
                        $estimasiPoin = $s->detail->sum(fn ($d) => floor($d->berat_estimasi_kg * ($d->jenisSampah?->poin_per_kg ?? 0)));
                        $bisaBatal = in_array($s->status, ['menunggu_kurir', 'menuju_tps'], true);

                        $tahapKe = match ($s->status) {
                            'menunggu_kurir', 'menuju_tps' => 1,
                            'kurir_ditugaskan', 'diangkut' => 2,
                            'tiba_di_tps' => 3,
                            default => 1,
                        };
                    @endphp

                    <article class="bg-white border-2 {{ $adaMedis ? 'border-[#B3261E]/60' : 'border-[#1D6B45]' }} rounded-xl overflow-hidden shadow-2xs">
                        {{-- Kepala Kartu Manifes --}}
                        <div class="px-5 py-3.5 {{ $adaMedis ? 'bg-[#FCE8E6]/60 border-b border-[#F2B8B5]' : 'bg-emerald-50/70 border-b border-emerald-200' }} flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-judul font-bold text-sm text-[#1F2421] angka-tabular">{{ $s->kode_setoran }}</span>

                                @if ($adaMedis)
                                    <span class="px-2 py-0.5 text-[11px] font-bold uppercase rounded bg-[#B3261E] text-white">
                                        Muatan Medis / Biohazard
                                    </span>
                                @endif

                                <span class="px-2.5 py-0.5 text-xs font-bold uppercase rounded bg-[#F2C230] text-[#1F2421]">
                                    {{ str_replace('_', ' ', $s->status) }}
                                </span>

                                <span class="px-2 py-0.5 text-xs font-medium rounded bg-white border border-stone-300 text-stone-700">
                                    {{ $s->metode === 'dijemput' ? 'Dijemput Kurir' : 'Antar Sendiri' }}
                                </span>
                            </div>

                            <span class="text-xs text-stone-600 angka-tabular">
                                Dibuat: {{ $s->created_at?->format('d/m/Y H:i') }} WIB
                            </span>
                        </div>

                        {{-- Lini Masa Progres 3 Tahap --}}
                        <div class="px-5 py-3 bg-stone-50 border-b border-stone-200">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <div class="flex items-center gap-1.5 text-[#1D6B45]">
                                    <span class="w-5 h-5 rounded-full bg-[#1D6B45] text-white flex items-center justify-center text-[11px] font-bold">1</span>
                                    <span>Tiket Manifes</span>
                                </div>
                                <div class="flex-1 h-1 mx-2.5 rounded {{ $tahapKe >= 2 ? 'bg-[#1D6B45]' : 'bg-stone-300' }}"></div>
                                <div class="flex items-center gap-1.5 {{ $tahapKe >= 2 ? 'text-[#1D6B45]' : 'text-stone-400' }}">
                                    <span class="w-5 h-5 rounded-full {{ $tahapKe >= 2 ? 'bg-[#1D6B45] text-white' : 'bg-stone-200 text-stone-500' }} flex items-center justify-center text-[11px] font-bold">2</span>
                                    <span>{{ $s->metode === 'dijemput' ? 'Diangkut Kurir' : 'Perjalanan ke Pos' }}</span>
                                </div>
                                <div class="flex-1 h-1 mx-2.5 rounded {{ $tahapKe >= 3 ? 'bg-[#1D6B45]' : 'bg-stone-300' }}"></div>
                                <div class="flex items-center gap-1.5 {{ $tahapKe >= 3 ? 'text-[#1D6B45]' : 'text-stone-400' }}">
                                    <span class="w-5 h-5 rounded-full {{ $tahapKe >= 3 ? 'bg-[#1D6B45] text-white' : 'bg-stone-200 text-stone-500' }} flex items-center justify-center text-[11px] font-bold">3</span>
                                    <span>Tiba &amp; Timbang</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            {{-- Info Tujuan & Kurir --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="p-3 rounded-lg bg-stone-50 border border-stone-200">
                                    <div class="text-stone-500 font-semibold">Fasilitas Penampungan Tujuan:</div>
                                    <div class="font-bold text-sm text-[#1F2421] mt-0.5">{{ $s->tps?->nama_tps ?? '-' }}</div>
                                    <div class="text-stone-600 mt-0.5">{{ $s->tps?->alamat }}</div>
                                </div>

                                @if ($s->metode === 'dijemput')
                                    <div class="p-3 rounded-lg bg-stone-50 border border-stone-200">
                                        <div class="text-stone-500 font-semibold">Kurir Penjemput:</div>
                                        @if ($s->penjemputan?->kurirUser)
                                            <div class="font-bold text-sm text-[#1F2421] mt-0.5">
                                                {{ $s->penjemputan->kurirUser->nama }}
                                            </div>
                                            <div class="text-stone-600 mt-0.5 angka-tabular">
                                                HP: {{ $s->penjemputan->kurirUser->no_hp }}
                                                @if ($s->penjemputan->kurir)
                                                    · {{ $s->penjemputan->kurir->jenis_kendaraan }} ({{ $s->penjemputan->kurir->plat_nomor }})
                                                @endif
                                            </div>
                                        @else
                                            <div class="font-semibold text-amber-800 mt-0.5">Menunggu kurir terdekat mengambil tugas…</div>
                                            <div class="text-stone-500 mt-0.5 truncate">
                                                Titik jemput: {{ $s->penjemputan?->alamat?->alamat_lengkap ?? '-' }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="p-3 rounded-lg bg-stone-50 border border-stone-200">
                                        <div class="text-stone-500 font-semibold">Pengantaran Mandiri:</div>
                                        <div class="font-bold text-sm text-[#1F2421] mt-0.5">Serahkan ke Meja Timbang Pos</div>
                                        <div class="text-stone-600 mt-0.5 angka-tabular">
                                            Jam Buka: {{ substr((string) $s->tps?->jam_buka, 0, 5) }} – {{ substr((string) $s->tps?->jam_tutup, 0, 5) }} WIB
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Daftar Rincian Item Limbah --}}
                            <div>
                                <div class="text-xs font-semibold text-stone-600 mb-2 flex items-center justify-between angka-tabular">
                                    <span>Rincian Item Manifes:</span>
                                    <span>Total Estimasi: <strong>{{ number_format($totalEstKg, 0, ',', '.') }} item</strong> · Est. Poin B3: <strong class="text-[#1D6B45]">+{{ $estimasiPoin }} pts</strong></span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($s->detail as $d)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md border {{ $d->jenisSampah?->kategori === 'medis' ? 'border-[#F2B8B5] bg-[#FFFBFB] text-[#B3261E]' : 'border-stone-200 bg-stone-50 text-stone-800' }} text-xs angka-tabular">
                                            <span class="font-semibold">{{ $d->jenisSampah?->nama }}</span>
                                            <span>({{ number_format($d->berat_estimasi_kg, 0, ',', '.') }} item)</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            @if ($bisaBatal)
                                <div class="pt-3 border-t border-stone-200 flex items-center justify-between">
                                    <span class="text-xs text-stone-500">Manifes masih dapat dibatalkan sebelum diangkut kurir / tiba di fasilitas.</span>
                                    <form method="POST" action="{{ route('pengguna.setoran.batal', $s) }}" onsubmit="return confirm('Batalkan manifes setoran ini?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-red-300 text-red-700 hover:bg-red-50 text-xs font-semibold cursor-pointer">
                                            Batalkan Manifes
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="bg-white border border-stone-200 rounded-xl p-8 text-center space-y-3">
                        <div class="text-sm font-bold text-[#1F2421]">Tidak ada manifes penyetoran yang sedang berjalan</div>
                        <p class="text-xs text-stone-500 max-w-md mx-auto">
                            Sudah memilah limbah medis dalam <em>Safety Box</em> atau mengumpulkan baterai bekas, lampu B3, dan sampah terpilah? Buat tiket manifes sekarang.
                        </p>
                        <div>
                            <a href="{{ route('pengguna.setoran.create') }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs font-semibold cursor-pointer">
                                <span>+ Buat Manifes Setoran Baru</span>
                            </a>
                        </div>
                    </div>
                @endforelse
            </section>

            {{-- 2. RINGKASAN RIWAYAT SETORAN TERAKHIR --}}
            <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-stone-200 flex items-center justify-between">
                    <h2 class="text-base font-bold text-[#1F2421]">Riwayat Manifes Terakhir</h2>
                    <a href="{{ route('pengguna.riwayat') }}" class="text-xs font-semibold text-[#1D6B45] hover:underline">
                        Lihat Semua &amp; Beri Ulasan →
                    </a>
                </div>

                @if ($setoranTerakhir->isEmpty())
                    <div class="p-6 text-xs text-stone-500">Belum ada riwayat manifes setoran.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-stone-50 border-b border-stone-200 text-xs font-semibold text-stone-600">
                                    <th class="py-2.5 px-4">Kode &amp; Tanggal</th>
                                    <th class="py-2.5 px-4">Fasilitas Tujuan</th>
                                    <th class="py-2.5 px-4">Status</th>
                                    <th class="py-2.5 px-4 text-right">Jumlah Item / Poin</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-200 text-xs">
                                @foreach ($setoranTerakhir as $st)
                                    <tr class="hover:bg-stone-50/70">
                                        <td class="py-3 px-4 angka-tabular">
                                            <div class="font-bold text-[#1F2421]">{{ $st->kode_setoran }}</div>
                                            <div class="text-stone-500">{{ $st->created_at?->format('d/m/Y H:i') }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="font-semibold text-[#1F2421]">{{ $st->tps?->nama_tps ?? '-' }}</div>
                                            <div class="text-stone-500">{{ $st->metode === 'dijemput' ? 'Dijemput Kurir' : 'Antar Sendiri' }}</div>
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded font-bold uppercase text-[11px] {{ $st->status === 'selesai' ? 'bg-emerald-100 text-emerald-900' : ($st->status === 'ditolak_tps' || $st->status === 'dibatalkan' ? 'bg-red-100 text-red-900' : 'bg-amber-100 text-amber-900') }}">
                                                {{ str_replace('_', ' ', $st->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right angka-tabular">
                                            @if ($st->total_berat_aktual_kg !== null)
                                                <div class="font-bold text-[#1F2421]">{{ number_format($st->total_berat_aktual_kg, 0, ',', '.') }} item</div>
                                                <div class="font-semibold text-[#1D6B45]">+{{ $st->total_poin }} pts</div>
                                            @else
                                                <span class="text-stone-400">Est. {{ number_format($st->detail->sum('berat_estimasi_kg'), 0, ',', '.') }} item</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        {{-- KOLOM KANAN (1 Kolom): NOTIFIKASI & FASILITAS BERIZIN --}}
        <div class="space-y-6">

            {{-- 1. PEMBERITAHUAN MANIFES --}}
            <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3.5 border-b border-stone-200 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[#1F2421]">Pemberitahuan Terbaru</h2>
                    @if ($notifikasiBelumBaca->isNotEmpty())
                        <form method="POST" action="{{ route('pengguna.notifikasi.baca') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-[#1D6B45] hover:underline cursor-pointer">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                </div>

                @if ($notifikasiBelumBaca->isEmpty())
                    <div class="p-4 text-xs text-stone-500">
                        Tidak ada pemberitahuan baru yang belum dibaca.
                    </div>
                @else
                    <ul class="divide-y divide-stone-200 text-xs">
                        @foreach ($notifikasiBelumBaca as $n)
                            <li class="p-3.5 space-y-1 bg-emerald-50/30">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="font-semibold text-[#1F2421]">{{ $n->judul }}</span>
                                    <span class="text-stone-400 angka-tabular">{{ $n->created_at?->format('d/m H:i') }}</span>
                                </div>
                                <p class="text-stone-600">{{ $n->pesan }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- 2. DAFTAR FASILITAS PENAMPUNGAN BERIZIN --}}
            <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3.5 border-b border-stone-200">
                    <h2 class="text-sm font-bold text-[#1F2421]">Fasilitas Berizin Medis &amp; B3</h2>
                    <p class="text-[11px] text-stone-500">Status daya tampung fasilitas secara langsung.</p>
                </div>

                <div class="divide-y divide-stone-200 text-xs">
                    @foreach ($daftarTps as $tps)
                        @php
                            $sisaKg = max(0, (float) $tps->kapasitas_total_kg - (float) $tps->kapasitas_terpakai_kg);
                        @endphp
                        <div class="p-3.5 space-y-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-[#1F2421]">{{ $tps->nama_tps }}</span>
                                @if ($tps->izin_limbah_medis)
                                    <span class="px-1.5 py-0.5 rounded bg-[#FCE8E6] text-[#B3261E] font-bold text-[10px]">
                                        Izin Medis
                                    </span>
                                @endif
                            </div>
                            <p class="text-stone-600">{{ $tps->alamat }}</p>
                            <div class="flex items-center justify-between text-stone-500 angka-tabular pt-1">
                                <span>Sisa Kuota: <strong class="text-[#1D6B45]">{{ number_format($sisaKg, 0, ',', '.') }} kg</strong></span>
                                <span class="uppercase font-bold {{ $tps->status === 'buka' ? 'text-emerald-700' : 'text-red-700' }}">{{ $tps->status }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    </div>

@endsection

