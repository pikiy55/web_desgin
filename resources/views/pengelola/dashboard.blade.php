@extends('layouts.pengelola')

@section('title', 'Meja Timbang & Manifes Masuk')
@section('heading', $tps ? 'Meja Timbang Manifes · '.$tps->nama_tps : 'Pos Pengelola Fasilitas Medis & B3')
@section('subheading', $tps ? $tps->alamat.($tps->izin_limbah_medis ? ' · Berizin Resmi Limbah Medis & B3' : ' · Fasilitas Limbah B3 & Daur Ulang') : 'Daftarkan fasilitas penampungan Anda untuk mulai menerima manifes limbah.')

@section('content')

    @if (! $tps)
        <section class="bg-white border border-stone-200 rounded-xl p-6 max-w-xl">
            <h2 class="text-base font-bold text-[#1F2421]">Anda Belum Memiliki Lokasi TPS</h2>
            <p class="text-sm text-stone-600 mt-1 mb-4">
                Daftarkan data TPS yang Anda kelola agar warga dan kurir dapat mengirim setoran sampah ke lokasi Anda.
            </p>
            <a href="{{ route('pengelola.profil-tps') }}" class="inline-block px-4 py-2.5 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white text-sm font-semibold">
                Daftarkan Lokasi TPS Sekarang
            </a>
        </section>
    @else

        @if (! $tps->is_verified)
            <div role="note" class="p-4 rounded-xl bg-amber-50 border border-amber-300 text-amber-950 text-sm">
                <strong>Menunggu Verifikasi Admin:</strong> Lokasi <strong>{{ $tps->nama_tps }}</strong> sedang diperiksa oleh Administrator. TPS akan tampil di rekomendasi warga setelah disetujui.
            </div>
        @endif

        {{-- Pita Status Pos Timbang & Kapasitas TPS --}}
        @php
            $persenTps = $tps->kapasitas_total_kg > 0
                ? min(100, round(($tps->kapasitas_terpakai_kg / $tps->kapasitas_total_kg) * 100, 1))
                : 0;
            $sisaTpsKg = max(0, $tps->kapasitas_total_kg - $tps->kapasitas_terpakai_kg);
        @endphp

        <section aria-label="Status Pos Timbang" class="bg-white border border-stone-200 rounded-xl overflow-hidden">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 divide-y sm:divide-y-0 sm:divide-x divide-stone-200">
                <div class="p-4">
                    <div class="text-xs text-stone-500">Siap Ditimbang di Pos</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-bold angka-tabular {{ $ringkasan['siap_timbang'] > 0 ? 'text-[#1D6B45]' : 'text-[#1F2421]' }}">
                            {{ $ringkasan['siap_timbang'] }}
                        </span>
                        <span class="text-xs text-stone-500">setoran tiba</span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="text-xs text-stone-500">Sedang Menuju TPS</div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-2xl font-bold angka-tabular text-amber-700">{{ $ringkasan['sedang_menuju'] }}</span>
                        <span class="text-xs text-stone-500">perjalanan</span>
                    </div>
                </div>

                <div class="p-4">
                    <div class="text-xs text-stone-500">Ditimbang Hari Ini</div>
                    <div class="mt-1 flex items-baseline gap-1.5">
                        <span class="text-2xl font-bold angka-tabular text-[#1F2421]">
                            {{ number_format($ringkasan['berat_hari_ini_kg'], 1, ',', '.') }}
                        </span>
                        <span class="text-xs text-stone-500">kg ({{ $ringkasan['selesai_hari_ini'] }} setoran)</span>
                    </div>
                </div>

                <div class="p-4 lg:col-span-2">
                    <div class="flex items-center justify-between text-xs mb-1 angka-tabular">
                        <span class="text-stone-500">Kapasitas TPS Terpakai</span>
                        <span class="font-semibold text-[#1F2421]">
                            {{ number_format($tps->kapasitas_terpakai_kg, 1, ',', '.') }} / {{ number_format($tps->kapasitas_total_kg, 0, ',', '.') }} kg ({{ $persenTps }}%)
                        </span>
                    </div>
                    <div class="w-full h-2.5 rounded-full bg-stone-200 overflow-hidden">
                        <div class="h-full {{ $persenTps >= 90 ? 'bg-red-600' : ($persenTps >= 70 ? 'bg-amber-500' : 'bg-[#1D6B45]') }}"
                             style="width: {{ $persenTps }}%"></div>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-xs text-stone-500 angka-tabular">
                        <span>Sisa daya tampung: <strong>{{ number_format($sisaTpsKg, 1, ',', '.') }} kg</strong></span>
                        <a href="{{ route('pengelola.kapasitas') }}" class="font-semibold text-[#1D6B45] hover:underline">Kosongkan petak</a>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            {{-- KOLOM UTAMA: MEJA TIMBANG DIGITAL & ANTREAN SETORAN MASUK (2 Kolom) --}}
            <section class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-[#1F2421]">Antrean Manifes Masuk &amp; Meja Timbang Limbah Medis / B3</h2>
                        <p class="text-xs text-stone-500">Periksa keutuhan segel kemasan K3 (Safety Box / Kantong Biohazard), lalu isi berat aktual (kg) hasil timbangan.</p>
                    </div>
                </div>

                @forelse ($setoranMasuk as $s)
                    @php
                        $sudahTiba = $s->status === 'tiba_di_tps';
                        $totalEstimasi = $s->detail->sum('berat_estimasi_kg');
                        $estimasiPoin = $s->detail->sum(fn ($d) => floor($d->berat_estimasi_kg * ($d->jenisSampah?->poin_per_kg ?? 0)));
                    @endphp

                    <article class="bg-white border-2 {{ $sudahTiba ? 'border-[#1D6B45]' : 'border-stone-200' }} rounded-xl overflow-hidden"
                             data-meja-timbang="{{ $s->setoran_id }}">
                        {{-- Kepala Kartu Setoran --}}
                        <div class="px-5 py-3.5 {{ $sudahTiba ? 'bg-emerald-50/70 border-b border-emerald-200' : 'bg-stone-50 border-b border-stone-200' }} flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <span class="font-judul font-bold text-sm text-[#1F2421] angka-tabular">{{ $s->kode_setoran }}</span>

                                @if ($sudahTiba)
                                    <span class="px-2.5 py-0.5 text-xs font-bold uppercase rounded bg-[#F2C230] text-[#1F2421]">
                                        Tiba di TPS · Siap Timbang
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 text-xs font-semibold uppercase rounded bg-amber-100 text-amber-900">
                                        {{ str_replace('_', ' ', $s->status) }}
                                    </span>
                                @endif

                                <span class="px-2 py-0.5 text-xs font-medium rounded border border-stone-300 bg-white text-stone-700">
                                    {{ $s->metode === 'dijemput' ? 'Diantar Kurir' : 'Antar Sendiri' }}
                                </span>
                            </div>

                            <div class="text-xs text-stone-600 angka-tabular">
                                @if ($s->estimasi_tiba)
                                    Estimasi tiba: <strong>{{ $s->estimasi_tiba->format('d/m H:i') }}</strong>
                                @else
                                    Dibuat: {{ $s->created_at?->format('d/m H:i') }}
                                @endif
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            {{-- Info Penyetor & Kurir --}}
                            <div class="flex flex-wrap items-start justify-between gap-4 text-xs">
                                <div>
                                    <span class="text-stone-500">Penyetor (Warga):</span>
                                    <div class="font-semibold text-sm text-[#1F2421]">{{ $s->pengguna?->nama ?? '-' }}</div>
                                    <div class="text-stone-500 angka-tabular">{{ $s->pengguna?->no_hp }}</div>
                                </div>

                                @if ($s->metode === 'dijemput')
                                    <div>
                                        <span class="text-stone-500">Kurir Pengantar:</span>
                                        <div class="font-semibold text-sm text-[#1F2421]">
                                            {{ $s->penjemputan?->kurirUser?->nama ?? 'Menunggu kurir' }}
                                        </div>
                                        @if ($s->penjemputan?->alamat)
                                            <div class="text-stone-500 max-w-xs truncate">{{ $s->penjemputan->alamat->alamat_lengkap }}</div>
                                        @endif
                                    </div>
                                @endif

                                @if ($s->catatan_pengguna)
                                    <div class="p-2.5 rounded-lg bg-stone-50 border border-stone-200 text-stone-700 max-w-xs">
                                        <span class="font-semibold">Catatan warga:</span> "{{ $s->catatan_pengguna }}"
                                    </div>
                                @endif
                            </div>

                            {{-- Form Penimbangan per Jenis Sampah --}}
                            <form method="POST" action="{{ route('pengelola.setoran.timbang', $s) }}" class="space-y-4">
                                @csrf

                                <div class="border border-stone-200 rounded-lg overflow-hidden">
                                    <table class="w-full text-left border-collapse text-sm">
                                        <thead>
                                            <tr class="bg-stone-100 border-b border-stone-200 text-xs font-semibold text-stone-600">
                                                <th class="py-2.5 px-3">Jenis Limbah</th>
                                                <th class="py-2.5 px-3 text-right">Tarif Poin</th>
                                                <th class="py-2.5 px-3 text-right">Estimasi Warga</th>
                                                <th class="py-2.5 px-3 text-right w-44">Jumlah Aktual (item)</th>
                                                <th class="py-2.5 px-3 text-right">Poin</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-stone-200 angka-tabular">
                                            @foreach ($s->detail as $d)
                                                @php
                                                    $poinPerKg = $d->jenisSampah?->poin_per_kg ?? 0;
                                                    $nilaiAwalKg = old("berat_aktual.{$d->detail_id}", (float) ($d->berat_aktual_kg ?? $d->berat_estimasi_kg));
                                                    $poinItem = (int) floor(((float) $nilaiAwalKg) * $poinPerKg);
                                                @endphp
                                                <tr>
                                                    <td class="py-2.5 px-3">
                                                        <div class="font-semibold text-[#1F2421]">{{ $d->jenisSampah?->nama ?? '-' }}</div>
                                                        <div class="text-xs text-stone-500 uppercase">{{ $d->jenisSampah?->kategori }}</div>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right text-xs text-stone-600">
                                                        {{ $poinPerKg }} pts/item
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right text-xs text-stone-600">
                                                        {{ number_format($d->berat_estimasi_kg, 0, ',', '.') }} item
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right">
                                                        <div class="inline-flex items-center justify-end gap-1.5">
                                                            <input type="number"
                                                                   name="berat_aktual[{{ $d->detail_id }}]"
                                                                   step="any"
                                                                   min="0.01"
                                                                   max="99999"
                                                                   required
                                                                   value="{{ $nilaiAwalKg }}"
                                                                   data-input-berat
                                                                   data-poin-per-kg="{{ $poinPerKg }}"
                                                                   aria-label="Jumlah aktual (item) {{ $d->jenisSampah?->nama }}"
                                                                   class="w-24 px-2.5 py-1.5 rounded-lg border border-stone-300 focus:border-[#1D6B45] bg-white text-sm font-bold text-right text-[#1F2421]">
                                                            <span class="text-xs font-semibold text-stone-600">item</span>
                                                        </div>
                                                    </td>
                                                    <td class="py-2.5 px-3 text-right font-semibold text-[#1D6B45]" data-poin-item>
                                                        +{{ $poinItem }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-stone-50 border-t border-stone-200 font-bold text-sm angka-tabular">
                                                <td colspan="2" class="py-2.5 px-3 text-xs uppercase text-stone-600">Total Item Diterima</td>
                                                <td class="py-2.5 px-3 text-right text-xs text-stone-500">
                                                    Est: {{ number_format($totalEstimasi, 0, ',', '.') }} item
                                                </td>
                                                <td class="py-2.5 px-3 text-right text-[#1F2421]" data-total-berat>
                                                    {{ number_format($totalEstimasi, 0, ',', '.') }} item
                                                </td>
                                                <td class="py-2.5 px-3 text-right text-[#1D6B45]" data-total-poin>
                                                    +{{ $estimasiPoin }} pts
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="text-xs text-stone-500">
                                        Menyimpan timbangan akan otomatis menambah kapasitas terpakai TPS & mengirim poin ke warga.
                                    </div>

                                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs sm:text-sm font-semibold cursor-pointer">
                                        Simpan Timbangan & Selesaikan Setoran
                                    </button>
                                </div>
                            </form>

                            {{-- Baris Tindakan Tambahan: Konfirmasi Tiba atau Tolak Setoran --}}
                            <div class="pt-3 border-t border-stone-200 flex flex-wrap items-center justify-between gap-3">
                                @if (! $sudahTiba && in_array($s->status, ['menuju_tps', 'diangkut'], true))
                                    <form method="POST" action="{{ route('pengelola.setoran.tiba', $s) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg border border-stone-300 bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-semibold cursor-pointer">
                                            Tandai Sudah Tiba di Pos TPS
                                        </button>
                                    </form>
                                @else
                                    <span></span>
                                @endif

                                <details class="text-xs">
                                    <summary class="text-red-700 font-semibold cursor-pointer hover:underline">
                                        Tolak setoran ini…
                                    </summary>
                                    <form method="POST" action="{{ route('pengelola.setoran.tolak', $s) }}" class="mt-2 flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="text" name="alasan_penolakan" required maxlength="255"
                                               placeholder="Alasan penolakan (mis. tercampur limbah B3)"
                                               class="px-2.5 py-1.5 rounded border border-red-300 bg-white text-xs w-64">
                                        <button type="submit" class="px-3 py-1.5 rounded bg-red-700 hover:bg-red-800 text-white text-xs font-semibold cursor-pointer">
                                            Konfirmasi Tolak
                                        </button>
                                    </form>
                                </details>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="bg-white border border-stone-200 rounded-xl p-8 text-center space-y-1">
                        <div class="text-sm font-semibold text-[#1F2421]">Tidak ada antrean setoran yang menunggu ditimbang</div>
                        <p class="text-xs text-stone-500">Setoran baru yang diajukan warga atau sedang dibawa kurir menuju TPS akan muncul di meja timbang ini.</p>
                    </div>
                @endforelse
            </section>

            {{-- KOLOM KANAN: PETAK KAPASITAS PER JENIS & NOTIFIKASI POS --}}
            <div class="space-y-6">
                {{-- Ringkasan Petak Kapasitas per Jenis Sampah --}}
                <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                    <div class="px-4 py-3.5 border-b border-stone-200 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-[#1F2421]">Petak Kapasitas per Jenis</h2>
                        <a href="{{ route('pengelola.kapasitas') }}" class="text-xs font-semibold text-[#1D6B45] hover:underline">
                            Atur / Kosongkan
                        </a>
                    </div>

                    @if ($kapasitasJenis->isEmpty())
                        <div class="p-4 text-xs text-stone-500">
                            Belum ada petak jenis sampah yang diatur.
                        </div>
                    @else
                        <ul class="divide-y divide-stone-200 text-xs angka-tabular">
                            @foreach ($kapasitasJenis as $kj)
                                @php
                                    $maks = (float) $kj->pivot->kapasitas_maks_kg;
                                    $isi = (float) $kj->pivot->terpakai_kg;
                                    $persenItem = $maks > 0 ? min(100, round(($isi / $maks) * 100, 0)) : 0;
                                @endphp
                                <li class="p-3 space-y-1.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-[#1F2421]">{{ $kj->nama }}</span>
                                        <span class="text-stone-600">
                                            <strong>{{ number_format($isi, 1, ',', '.') }}</strong> / {{ number_format($maks, 0, ',', '.') }} kg
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-stone-200 overflow-hidden">
                                        <div class="h-full {{ $persenItem >= 90 ? 'bg-red-600' : ($persenItem >= 70 ? 'bg-amber-500' : 'bg-[#1D6B45]') }}"
                                             style="width: {{ $persenItem }}%"></div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                {{-- Notifikasi Masuk Pos TPS --}}
                <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
                    <div class="px-4 py-3.5 border-b border-stone-200 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-[#1F2421]">Pemberitahuan Pos</h2>
                        @if ($notifikasiBelumBaca->isNotEmpty())
                            <form method="POST" action="{{ route('pengelola.notifikasi.baca') }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-[#1D6B45] hover:underline cursor-pointer">
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
                            @foreach ($notifikasiBelumBaca as $n)
                                <li class="p-3.5 space-y-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="font-semibold text-[#1F2421]">{{ $n->judul }}</span>
                                        <span class="text-stone-400 angka-tabular">{{ $n->created_at?->format('H:i') }}</span>
                                    </div>
                                    <p class="text-stone-600">{{ $n->pesan }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>

    @endif

    {{-- Kalkulator Poin & Berat Langsung pada Meja Timbang --}}
    <script>
        document.querySelectorAll('[data-meja-timbang]').forEach((kartu) => {
            const inputs = kartu.querySelectorAll('[data-input-berat]');
            const elTotalBerat = kartu.querySelector('[data-total-berat]');
            const elTotalPoin = kartu.querySelector('[data-total-poin]');

            const hitungUlang = () => {
                let totalBerat = 0;
                let totalPoin = 0;

                inputs.forEach((inp) => {
                    const berat = Math.max(0, parseFloat(inp.value) || 0);
                    const tarif = parseFloat(inp.dataset.poinPerKg) || 0;
                    const poin = Math.floor(berat * tarif);

                    totalBerat += berat;
                    totalPoin += poin;

                    const baris = inp.closest('tr');
                    const elPoinItem = baris ? baris.querySelector('[data-poin-item]') : null;
                    if (elPoinItem) {
                        elPoinItem.textContent = '+' + poin;
                    }
                });

                if (elTotalBerat) {
                    elTotalBerat.textContent = totalBerat.toLocaleString('id-ID', {
                        maximumFractionDigits: 2
                    }) + ' item';
                }
                if (elTotalPoin) {
                    elTotalPoin.textContent = '+' + Math.floor(totalPoin) + ' pts';
                }
            };

            inputs.forEach((inp) => inp.addEventListener('input', hitungUlang));
        });
    </script>

@endsection

