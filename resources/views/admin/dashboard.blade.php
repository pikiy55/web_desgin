@extends('layouts.admin')

@section('title', 'Ikhtisar & Verifikasi Izin Medis/B3')
@section('heading', 'Pengawasan Limbah Medis, B3 & Verifikasi Izin')
@section('subheading', 'Verifikasi izin fasilitas penampungan medis/B3 & kurir khusus, pantau daya tampung, dan awasi manifes setoran.')

@section('content')

    {{-- Pita Status Operasional (Menggunakan gap-px bg-[#D6DDD7] agar garis pemisah rapi di layar 2, 3, maupun 6 kolom) --}}
    <section aria-label="Ringkasan Operasional" class="bg-[#D6DDD7] border border-[#D6DDD7] rounded-2xl overflow-hidden shadow-2xs">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-px bg-[#D6DDD7]">
            <div class="bg-white p-4">
                <div class="text-[13px] font-medium text-[#4A554E]">Akun Menunggu</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold angka-tabular {{ $ringkasan['akun_pending'] > 0 ? 'text-[#B26A00]' : 'text-[#1F2421]' }}">
                        {{ $ringkasan['akun_pending'] }}
                    </span>
                    <span class="text-xs font-medium text-[#4A554E]">pendaftar</span>
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-[13px] font-medium text-[#4A554E]">TPS Belum Verifikasi</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold angka-tabular {{ $ringkasan['tps_belum_verifikasi'] > 0 ? 'text-[#B26A00]' : 'text-[#1F2421]' }}">
                        {{ $ringkasan['tps_belum_verifikasi'] }}
                    </span>
                    <span class="text-xs font-medium text-[#4A554E]">lokasi</span>
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-[13px] font-medium text-[#4A554E]">TPS Buka &amp; Aktif</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold angka-tabular text-[#1D6B45]">{{ $ringkasan['tps_aktif'] }}</span>
                    <span class="text-xs font-medium text-[#4A554E]">beroperasi</span>
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-[13px] font-medium text-[#4A554E]">Kurir Online/Tugas</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold angka-tabular text-[#1F2421]">{{ $ringkasan['kurir_online'] }}</span>
                    <span class="text-xs font-medium text-[#4A554E]">kurir</span>
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-[13px] font-medium text-[#4A554E]">Setoran Berjalan</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold angka-tabular text-[#1F2421]">{{ $ringkasan['setoran_berjalan'] }}</span>
                    <span class="text-xs font-medium text-[#4A554E]">transaksi</span>
                </div>
            </div>

            <div class="bg-white p-4">
                <div class="text-[13px] font-medium text-[#4A554E]">Total Ditimbang</div>
                <div class="mt-1 flex items-baseline gap-1.5">
                    <span class="text-2xl font-bold angka-tabular text-[#1F2421]">
                        {{ number_format($ringkasan['total_berat_selesai_kg'], 1, ',', '.') }}
                    </span>
                    <span class="text-xs font-semibold text-[#4A554E]">kg</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ANTREAN VERIFIKASI (Aksi Utama Admin dengan Konteks Izin & Konfirmasi) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- 1. Antrean Verifikasi Akun Pengelola TPS & Kurir --}}
        <section class="bg-white border border-[#D6DDD7] rounded-2xl flex flex-col overflow-hidden">
            <div class="px-5 py-4 border-b border-[#D6DDD7] flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-[#1F2421]">Verifikasi Pendaftaran Akun</h2>
                    <p class="text-[13px] text-[#4A554E]">Periksa identitas, SIM, atau afiliasi sebelum menyetujui akses.</p>
                </div>
                <a href="{{ route('admin.akun', ['status' => 'pending']) }}" class="text-[13px] font-semibold text-[#1D6B45] hover:underline shrink-0">
                    Akun &amp; Verifikasi Peran ({{ $ringkasan['akun_pending'] }}) →
                </a>
            </div>

            @if ($akunPending->isEmpty())
                <div class="p-6 text-sm text-[#4A554E]">
                    Tidak ada antrean pendaftaran akun yang menunggu verifikasi saat ini.
                </div>
            @else
                <div class="divide-y divide-[#D6DDD7]">
                    @foreach ($akunPending as $u)
                        <div class="p-4 space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                <div class="min-w-0 space-y-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-sm text-[#1F2421]">{{ $u->nama }}</span>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded {{ $u->role === 'kurir' ? 'bg-[#E3EFE7] text-[#133A26]' : 'bg-[#FFF8E1] text-[#7A5200] border border-[#F2C230]' }}">
                                            {{ $u->role === 'kurir' ? 'Kurir Angkut B3' : 'Pengelola Fasilitas/TPS' }}
                                        </span>
                                    </div>
                                    <div class="text-[13px] text-[#4A554E] angka-tabular">
                                        {{ $u->email }} · WA: {{ $u->no_hp }}
                                    </div>

                                    {{-- Konteks Dokumen / Kendaraan di tempat yang sama --}}
                                    @if ($u->kurirProfil)
                                        <div class="p-2.5 rounded-xl bg-[#F3F5F2] border border-[#D6DDD7] text-[13px] text-[#1F2421] angka-tabular space-y-0.5 mt-1.5">
                                            <div>
                                                <span class="text-[#4A554E]">Armada:</span>
                                                <strong>{{ $u->kurirProfil->jenis_kendaraan }}</strong>
                                                ({{ $u->kurirProfil->plat_nomor }})
                                                @if ($u->kurirProfil->kapasitas_angkut_kg)
                                                    · Maks {{ number_format($u->kurirProfil->kapasitas_angkut_kg, 0, ',', '.') }} kg
                                                @endif
                                            </div>
                                            <div>
                                                <span class="text-[#4A554E]">Nomor SIM:</span>
                                                <strong>{{ $u->kurirProfil->no_sim ?: 'Belum dicantumkan' }}</strong>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-2.5 rounded-xl bg-[#F3F5F2] border border-[#D6DDD7] text-[13px] text-[#4A554E] mt-1.5">
                                            Pendaftar peran <strong>Pengelola Fasilitas/TPS</strong> · Terdaftar {{ $u->created_at?->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('admin.akun', ['cari' => $u->email]) }}"
                                       class="px-2.5 py-1.5 rounded-lg border border-[#D6DDD7] text-[#1F2421] hover:bg-[#F3F5F2] text-xs font-semibold">
                                        Detail
                                    </a>

                                    <button type="button"
                                            onclick="bukaModalSetujuiAkun({{ $u->user_id }}, @js($u->nama), @js($u->role === 'kurir' ? 'Kurir Angkut B3' : 'Pengelola Fasilitas/TPS'), @js($u->kurirProfil ? ($u->kurirProfil->jenis_kendaraan.' · '.$u->kurirProfil->plat_nomor.' · SIM: '.($u->kurirProfil->no_sim ?: '-')) : $u->email))"
                                            class="px-3 py-1.5 rounded-lg bg-[#1D6B45] hover:bg-[#133A26] text-white text-xs font-semibold cursor-pointer">
                                        Setujui
                                    </button>

                                    <button type="button"
                                            onclick="bukaModalTolakAkun({{ $u->user_id }}, @js($u->nama))"
                                            class="px-3 py-1.5 rounded-lg border border-[#F2B8B5] text-[#B3261E] hover:bg-[#FCE8E6] text-xs font-semibold cursor-pointer">
                                        Tolak…
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- 2. Antrean Verifikasi TPS --}}
        <section class="bg-white border border-[#D6DDD7] rounded-2xl flex flex-col overflow-hidden">
            <div class="px-5 py-4 border-b border-[#D6DDD7] flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-[#1F2421]">TPS Menunggu Verifikasi Izin</h2>
                    <p class="text-[13px] text-[#4A554E]">Periksa nomor izin DLHKP/Kemenkes sebelum mengaktifkan rekomendasi.</p>
                </div>
                <a href="{{ route('admin.tps') }}" class="text-[13px] font-semibold text-[#1D6B45] hover:underline shrink-0">
                    Fasilitas &amp; Izin Medis/B3 →
                </a>
            </div>

            @if ($tpsBelumVerifikasi->isEmpty())
                <div class="p-6 text-sm text-[#4A554E]">
                    Semua lokasi TPS yang terdaftar sudah diverifikasi.
                </div>
            @else
                <div class="divide-y divide-[#D6DDD7]">
                    @foreach ($tpsBelumVerifikasi as $t)
                        <div class="p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-bold text-sm text-[#1F2421]">{{ $t->nama_tps }}</span>
                                    @if ($t->izin_limbah_medis)
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-[#FCE8E6] text-[#B3261E] border border-[#F2B8B5]">
                                            Pengajuan Izin Medis
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[13px] text-[#4A554E]">{{ $t->alamat }}</div>

                                {{-- Konteks Nomor Izin & Kapasitas --}}
                                <div class="p-2.5 rounded-xl bg-[#F3F5F2] border border-[#D6DDD7] text-[13px] text-[#1F2421] angka-tabular space-y-0.5 mt-1.5">
                                    <div>
                                        <span class="text-[#4A554E]">Nomor Izin Resmi:</span>
                                        <strong>{{ $t->no_izin ?: 'Belum dicantumkan' }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-[#4A554E]">Pengelola:</span>
                                        <strong>{{ $t->pengelola?->nama ?? '-' }}</strong>
                                        · Daya tampung {{ number_format($t->kapasitas_total_kg, 0, ',', '.') }} kg
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('admin.tps') }}"
                                   class="px-2.5 py-1.5 rounded-lg border border-[#D6DDD7] text-[#1F2421] hover:bg-[#F3F5F2] text-xs font-semibold">
                                    Detail Izin
                                </a>

                                <button type="button"
                                        onclick="bukaModalVerifikasiTps({{ $t->tps_id }}, @js($t->nama_tps), @js($t->no_izin ?: 'Belum ada nomor izin'), {{ $t->izin_limbah_medis ? 'true' : 'false' }})"
                                        class="px-3 py-1.5 rounded-lg bg-[#1D6B45] hover:bg-[#133A26] text-white text-xs font-semibold cursor-pointer">
                                    Verifikasi TPS…
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- PAPAN KAPASITAS TPS (Lembar Kendali Kapasitas Timbangan TPS) --}}
    <section class="bg-white border border-[#D6DDD7] rounded-2xl overflow-hidden">
        <div class="px-5 py-4 border-b border-[#D6DDD7] flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-[#1F2421]">Kapasitas &amp; Status TPS</h2>
                <p class="text-[13px] text-[#4A554E]">Kapasitas terpakai disinkronkan otomatis dari timbangan jenis limbah di tiap fasilitas (kuning mulai 75%, merah ≥ 90%).</p>
            </div>
            <a href="{{ route('admin.tps') }}" class="text-[13px] font-semibold text-[#1D6B45] hover:underline">
                Fasilitas &amp; Izin Medis/B3 →
            </a>
        </div>

        @if ($daftarTps->isEmpty())
            <div class="p-6 text-sm text-[#4A554E]">
                Belum ada TPS terdaftar. Tambahkan TPS baru melalui menu <a href="{{ route('admin.tps') }}" class="font-semibold text-[#1D6B45] underline">Fasilitas &amp; Izin Medis/B3</a>.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-[#D6DDD7] bg-[#F3F5F2] text-[13px] font-semibold text-[#4A554E]">
                            <th class="py-3 px-4">Nama TPS &amp; Alamat</th>
                            <th class="py-3 px-4">Pengelola</th>
                            <th class="py-3 px-4">Izin &amp; Verifikasi</th>
                            <th class="py-3 px-4 w-64">Keterisian Kapasitas (kg)</th>
                            <th class="py-3 px-4 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#D6DDD7]">
                        @foreach ($daftarTps as $tps)
                            @php
                                $persen = $tps->kapasitas_total_kg > 0
                                    ? min(100, round(($tps->kapasitas_terpakai_kg / $tps->kapasitas_total_kg) * 100, 1))
                                    : 0;
                                // Disamakan dengan landing page: kuning mulai di 75%, merah mulai di 90%
                                $warnaBar = $persen >= 90 ? 'bg-[#B3261E]' : ($persen >= 75 ? 'bg-[#D97706]' : 'bg-[#1D6B45]');
                            @endphp
                            <tr class="hover:bg-[#F3F5F2]/60">
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-sm text-[#1F2421]">{{ $tps->nama_tps }}</div>
                                    <div class="text-[13px] text-[#4A554E]">{{ $tps->alamat }}</div>
                                    @if ($tps->no_izin)
                                        <div class="text-xs text-[#4A554E] angka-tabular mt-0.5">No. Izin: {{ $tps->no_izin }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-[13px] text-[#1F2421]">
                                    <div class="font-semibold">{{ $tps->pengelola?->nama ?? '-' }}</div>
                                    <div class="text-[#4A554E] angka-tabular">{{ $tps->pengelola?->no_hp }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if ($tps->is_verified)
                                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-[#E3EFE7] text-[#133A26]">Terverifikasi</span>
                                        @else
                                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-[#FFF8E1] text-[#7A5200] border border-[#F2C230]">Belum Verifikasi</span>
                                        @endif

                                        @if ($tps->izin_limbah_medis)
                                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded bg-[#133A26] text-[#F2C230]">Izin Medis</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 angka-tabular">
                                    <div class="flex justify-between text-[13px] mb-1.5">
                                        <span class="font-semibold text-[#1F2421]">
                                            {{ number_format($tps->kapasitas_terpakai_kg, 1, ',', '.') }} / {{ number_format($tps->kapasitas_total_kg, 0, ',', '.') }} kg
                                        </span>
                                        <span class="font-semibold text-[#4A554E]">{{ $persen }}%</span>
                                    </div>
                                    <div class="w-full h-2.5 rounded-full bg-[#E2E8E3] overflow-hidden"
                                         role="progressbar"
                                         aria-valuenow="{{ $persen }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100"
                                         aria-label="Keterisian kapasitas {{ $tps->nama_tps }} {{ $persen }} persen">
                                        <div class="h-full {{ $warnaBar }}" style="width: {{ $persen }}%"></div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @php
                                        $badgeStatus = match ($tps->status) {
                                            'buka' => 'bg-[#E3EFE7] text-[#133A26] border-[#1D6B45]/30',
                                            'penuh' => 'bg-[#FCE8E6] text-[#B3261E] border-[#F2B8B5]',
                                            default => 'bg-[#F3F5F2] text-[#4A554E] border-[#D6DDD7]',
                                        };
                                    @endphp
                                    <span class="inline-block px-2.5 py-0.5 text-xs font-bold uppercase rounded border {{ $badgeStatus }}">
                                        {{ $tps->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- SETORAN TERBARU & LOG AKTIVITAS ADMIN --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Setoran Terbaru (2 kolom) --}}
        <section class="lg:col-span-2 bg-white border border-[#D6DDD7] rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-[#D6DDD7]">
                <h2 class="text-base font-bold text-[#1F2421]">Setoran Limbah Terbaru</h2>
                <p class="text-[13px] text-[#4A554E]">Pantauan transaksi penjemputan kurir maupun antar sendiri ke fasilitas.</p>
            </div>

            @if ($setoranTerbaru->isEmpty())
                <div class="p-6 text-sm text-[#4A554E]">
                    Belum ada transaksi setoran yang tercatat.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-[#D6DDD7] bg-[#F3F5F2] text-[13px] font-semibold text-[#4A554E]">
                                <th class="py-3 px-4">Kode &amp; Waktu</th>
                                <th class="py-3 px-4">Pengguna &amp; TPS</th>
                                <th class="py-3 px-4">Metode</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Jumlah Item / Poin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#D6DDD7] text-[13px]">
                            @foreach ($setoranTerbaru as $s)
                                @php
                                    $infoStatus = match ($s->status) {
                                        'menunggu_kurir'   => ['kelas' => 'bg-[#FFF8E1] text-[#7A5200] border border-[#F2C230]', 'label' => 'Menunggu Kurir'],
                                        'kurir_ditugaskan' => ['kelas' => 'bg-[#FFF8E1] text-[#7A5200] border border-[#F2C230]', 'label' => 'Kurir Ditugaskan'],
                                        'diangkut'         => ['kelas' => 'bg-[#E3EFE7] text-[#133A26] border border-[#1D6B45]/30', 'label' => 'Diangkut Kurir'],
                                        'menuju_tps'       => ['kelas' => 'bg-[#E3EFE7] text-[#133A26] border border-[#1D6B45]/30', 'label' => 'Menuju TPS'],
                                        'tiba_di_tps'      => ['kelas' => 'bg-[#FFF8E1] text-[#7A5200] border border-[#F2C230]', 'label' => 'Siap Timbang'],
                                        'selesai'          => ['kelas' => 'bg-[#133A26] text-white border border-[#133A26]', 'label' => 'Selesai'],
                                        'ditolak_tps'      => ['kelas' => 'bg-[#FCE8E6] text-[#B3261E] border border-[#F2B8B5]', 'label' => 'Ditolak TPS'],
                                        'dibatalkan'       => ['kelas' => 'bg-[#F3F5F2] text-[#4A554E] border border-[#D6DDD7]', 'label' => 'Dibatalkan'],
                                        default            => ['kelas' => 'bg-[#F3F5F2] text-[#1F2421] border border-[#D6DDD7]', 'label' => ucfirst(str_replace('_', ' ', $s->status))],
                                    };
                                @endphp
                                <tr class="hover:bg-[#F3F5F2]/60">
                                    <td class="py-3 px-4 angka-tabular">
                                        <div class="font-bold text-sm text-[#1F2421]">{{ $s->kode_setoran }}</div>
                                        <div class="text-[#4A554E]">{{ $s->created_at?->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-sm text-[#1F2421]">{{ $s->pengguna?->nama ?? '-' }}</div>
                                        <div class="text-[#4A554E]">→ {{ $s->tps?->nama_tps ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($s->metode === 'dijemput')
                                            <span class="font-semibold text-[#133A26]">Dijemput Kurir</span>
                                            @if ($s->penjemputan?->kurirUser)
                                                <div class="text-[#4A554E]">{{ $s->penjemputan->kurirUser->nama }}</div>
                                            @endif
                                        @else
                                            <span class="font-semibold text-[#1F2421]">Antar Sendiri</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-block px-2.5 py-0.5 text-xs font-bold rounded {{ $infoStatus['kelas'] }}">
                                            {{ $infoStatus['label'] }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right angka-tabular">
                                        @if ($s->total_berat_aktual_kg !== null)
                                            <div class="font-bold text-sm text-[#1F2421]">{{ number_format($s->total_berat_aktual_kg, 0, ',', '.') }} item</div>
                                            <div class="text-[#1D6B45] font-semibold">+{{ $s->total_poin }} poin</div>
                                        @else
                                            <span class="text-[#4A554E] font-medium">Belum diverifikasi</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- Log Aktivitas Admin --}}
        <section class="bg-white border border-[#D6DDD7] rounded-2xl flex flex-col overflow-hidden">
            <div class="px-5 py-4 border-b border-[#D6DDD7]">
                <h2 class="text-base font-bold text-[#1F2421]">Log Aktivitas Admin</h2>
                <p class="text-[13px] text-[#4A554E]">Rekam jejak verifikasi, alasan penolakan, &amp; perubahan.</p>
            </div>

            @if ($logAdmin->isEmpty())
                <div class="p-6 text-sm text-[#4A554E]">
                    Belum ada aktivitas admin yang tercatat.
                </div>
            @else
                <ul class="divide-y divide-[#D6DDD7] text-[13px]">
                    @foreach ($logAdmin as $log)
                        <li class="p-4 space-y-1">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-sm text-[#1F2421]">{{ str_replace('_', ' ', ucfirst($log->aksi)) }}</span>
                                <span class="text-[#4A554E] font-medium angka-tabular">{{ $log->created_at?->format('d/m H:i') }}</span>
                            </div>
                            <div class="text-[#4A554E]">
                                Oleh <strong class="text-[#1F2421]">{{ $log->admin?->nama ?? 'Admin' }}</strong>
                                @if (!empty($log->detail['nama']))
                                    · {{ $log->detail['nama'] }}
                                @elseif (!empty($log->detail['nama_tps']))
                                    · {{ $log->detail['nama_tps'] }}
                                @elseif (!empty($log->detail['judul']))
                                    · {{ $log->detail['judul'] }}
                                @endif
                            </div>
                            @if (!empty($log->detail['catatan']))
                                <div class="mt-1 px-2.5 py-1.5 rounded-lg bg-[#F3F5F2] border border-[#D6DDD7] text-xs text-[#1F2421]">
                                    <span class="font-semibold text-[#4A554E]">Catatan/Alasan:</span> {{ $log->detail['catatan'] }}
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    {{-- MODAL 1: KONFIRMASI SETUJUI AKUN --}}
    <dialog id="modal-setujui-akun" class="rounded-2xl border border-[#D6DDD7] p-0 w-full max-w-md shadow-xl backdrop:bg-black/50">
        <form method="POST" id="form-setujui-akun" class="p-5 space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="aktif">

            <div class="flex items-start justify-between gap-3 border-b border-[#D6DDD7] pb-3">
                <div>
                    <h3 class="font-judul font-bold text-lg text-[#1F2421]">Konfirmasi Persetujuan Akun</h3>
                    <p class="text-[13px] text-[#4A554E]">Pastikan data pendaftar sudah sesuai dengan dokumen K3.</p>
                </div>
                <button type="button" onclick="document.getElementById('modal-setujui-akun').close()" class="text-[#4A554E] hover:text-[#1F2421] font-bold text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <div class="p-3.5 rounded-xl bg-[#F3F5F2] border border-[#D6DDD7] text-[13px] space-y-1">
                <div><span class="text-[#4A554E]">Nama Pendaftar:</span> <strong id="setujui-akun-nama" class="text-[#1F2421]">-</strong></div>
                <div><span class="text-[#4A554E]">Peran Diajukan:</span> <strong id="setujui-akun-peran" class="text-[#133A26]">-</strong></div>
                <div><span class="text-[#4A554E]">Rincian Verifikasi:</span> <span id="setujui-akun-info" class="text-[#1F2421] font-medium">-</span></div>
            </div>

            <div>
                <label for="catatan-setujui" class="block text-[13px] font-semibold text-[#1F2421] mb-1">Catatan Verifikasi (Opsional)</label>
                <input type="text" id="catatan-setujui" name="catatan" maxlength="255"
                       placeholder="Mis: SIM & STNK sesuai, diverifikasi via WA"
                       class="w-full px-3 py-2 rounded-xl border border-[#8A968E] text-sm text-[#1F2421]">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-setujui-akun').close()"
                        class="px-4 py-2 rounded-xl border border-[#D6DDD7] text-[#1F2421] text-xs font-semibold hover:bg-[#F3F5F2] cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D6B45] hover:bg-[#133A26] text-white text-xs font-semibold cursor-pointer">
                    Ya, Setujui &amp; Aktifkan
                </button>
            </div>
        </form>
    </dialog>

    {{-- MODAL 2: TOLAK PENDAFTARAN AKUN DENGAN ALASAN (TERCATAT DI LOG) --}}
    <dialog id="modal-tolak-akun" class="rounded-2xl border border-[#D6DDD7] p-0 w-full max-w-md shadow-xl backdrop:bg-black/50">
        <form method="POST" id="form-tolak-akun" class="p-5 space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="ditolak">

            <div class="flex items-start justify-between gap-3 border-b border-[#D6DDD7] pb-3">
                <div>
                    <h3 class="font-judul font-bold text-lg text-[#B3261E]">Tolak Pendaftaran Akun</h3>
                    <p class="text-[13px] text-[#4A554E]">Alasan penolakan akan disimpan di log audit &amp; dikirim ke pendaftar.</p>
                </div>
                <button type="button" onclick="document.getElementById('modal-tolak-akun').close()" class="text-[#4A554E] hover:text-[#1F2421] font-bold text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <div class="p-3 rounded-xl bg-[#FCE8E6]/60 border border-[#F2B8B5] text-[13px] text-[#1F2421]">
                Menolak pengajuan akun: <strong id="tolak-akun-nama">-</strong>
            </div>

            <div>
                <label for="catatan-tolak" class="block text-[13px] font-semibold text-[#1F2421] mb-1">
                    Alasan Penolakan <span class="text-[#B3261E]">*</span>
                </label>
                <textarea id="catatan-tolak" name="catatan" rows="3" required maxlength="255"
                          placeholder="Tuliskan alasan penolakan (contoh: Nomor SIM atau dokumen izin fasilitas belum valid)..."
                          class="w-full px-3 py-2 rounded-xl border border-[#8A968E] text-sm text-[#1F2421]"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-tolak-akun').close()"
                        class="px-4 py-2 rounded-xl border border-[#D6DDD7] text-[#1F2421] text-xs font-semibold hover:bg-[#F3F5F2] cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#B3261E] hover:bg-[#8C1D18] text-white text-xs font-semibold cursor-pointer">
                    Konfirmasi Tolak Akun
                </button>
            </div>
        </form>
    </dialog>

    {{-- MODAL 3: KONFIRMASI VERIFIKASI TPS & IZIN MEDIS --}}
    <dialog id="modal-verifikasi-tps" class="rounded-2xl border border-[#D6DDD7] p-0 w-full max-w-md shadow-xl backdrop:bg-black/50">
        <form method="POST" id="form-verifikasi-tps" class="p-5 space-y-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="is_verified" value="1">

            <div class="flex items-start justify-between gap-3 border-b border-[#D6DDD7] pb-3">
                <div>
                    <h3 class="font-judul font-bold text-lg text-[#1F2421]">Verifikasi Izin Operasional TPS</h3>
                    <p class="text-[13px] text-[#4A554E]">Pastikan nomor izin fasilitas telah sesuai sebelum diaktifkan.</p>
                </div>
                <button type="button" onclick="document.getElementById('modal-verifikasi-tps').close()" class="text-[#4A554E] hover:text-[#1F2421] font-bold text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <div class="p-3.5 rounded-xl bg-[#F3F5F2] border border-[#D6DDD7] text-[13px] space-y-1 angka-tabular">
                <div><span class="text-[#4A554E]">Nama Fasilitas:</span> <strong id="verif-tps-nama" class="text-[#1F2421]">-</strong></div>
                <div><span class="text-[#4A554E]">Nomor Izin Tercatat:</span> <strong id="verif-tps-izin" class="text-[#133A26]">-</strong></div>
            </div>

            <label class="flex items-start gap-2.5 p-3 rounded-xl border border-[#D6DDD7] bg-white cursor-pointer text-[13px]">
                <input type="checkbox" id="verif-tps-medis" name="izin_limbah_medis" value="1" class="mt-0.5 w-4 h-4 accent-[#1D6B45]">
                <span>
                    <strong class="text-[#1F2421] block">Sertakan Izin Limbah Medis &amp; B3</strong>
                    <span class="text-xs text-[#4A554E]">Centang jika fasilitas ini memiliki cold storage / izin resmi penampungan limbah medis fasyankes.</span>
                </span>
            </label>

            <div>
                <label for="catatan-verif-tps" class="block text-[13px] font-semibold text-[#1F2421] mb-1">Catatan Audit Izin (Opsional)</label>
                <input type="text" id="catatan-verif-tps" name="catatan" maxlength="255"
                       placeholder="Mis: SK Izin DLHKP terverifikasi aktif"
                       class="w-full px-3 py-2 rounded-xl border border-[#8A968E] text-sm text-[#1F2421]">
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-verifikasi-tps').close()"
                        class="px-4 py-2 rounded-xl border border-[#D6DDD7] text-[#1F2421] text-xs font-semibold hover:bg-[#F3F5F2] cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D6B45] hover:bg-[#133A26] text-white text-xs font-semibold cursor-pointer">
                    Sahkan &amp; Verifikasi TPS
                </button>
            </div>
        </form>
    </dialog>

    <script>
        function bukaModalSetujuiAkun(userId, nama, peran, info) {
            const modal = document.getElementById('modal-setujui-akun');
            const form = document.getElementById('form-setujui-akun');
            form.action = `{{ url('/admin/akun') }}/${userId}/status`;
            document.getElementById('setujui-akun-nama').textContent = nama;
            document.getElementById('setujui-akun-peran').textContent = peran;
            document.getElementById('setujui-akun-info').textContent = info;
            document.getElementById('catatan-setujui').value = '';
            modal.showModal();
        }

        function bukaModalTolakAkun(userId, nama) {
            const modal = document.getElementById('modal-tolak-akun');
            const form = document.getElementById('form-tolak-akun');
            form.action = `{{ url('/admin/akun') }}/${userId}/status`;
            document.getElementById('tolak-akun-nama').textContent = nama;
            document.getElementById('catatan-tolak').value = '';
            modal.showModal();
        }

        function bukaModalVerifikasiTps(tpsId, namaTps, noIzin, izinMedis) {
            const modal = document.getElementById('modal-verifikasi-tps');
            const form = document.getElementById('form-verifikasi-tps');
            form.action = `{{ url('/admin/tps') }}/${tpsId}/verifikasi`;
            document.getElementById('verif-tps-nama').textContent = namaTps;
            document.getElementById('verif-tps-izin').textContent = noIzin;
            document.getElementById('verif-tps-medis').checked = Boolean(izinMedis);
            document.getElementById('catatan-verif-tps').value = '';
            modal.showModal();
        }
    </script>

@endsection
