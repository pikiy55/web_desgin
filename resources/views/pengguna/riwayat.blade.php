@extends('layouts.pengguna')

@section('title', 'Riwayat Manifes & Ulasan')
@section('heading', 'Riwayat Manifes Setoran & Ulasan Layanan')
@section('subheading', 'Lacak jejak waktu perubahan status manifes, bandingkan berat aktual timbangan, dan berikan ulasan.')

@section('content')

    {{-- Filter Status Manifes --}}
    <section class="bg-white border border-stone-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1.5">
            @php
                $opsiFilter = [
                    ''            => 'Semua Manifes',
                    'berjalan'    => 'Sedang Berjalan',
                    'selesai'     => 'Selesai Ditimbang',
                    'ditolak_tps' => 'Ditolak Fasilitas',
                    'dibatalkan'  => 'Dibatalkan',
                ];
            @endphp
            @foreach ($opsiFilter as $kode => $label)
                <a href="{{ route('pengguna.riwayat', $kode !== '' ? ['status' => $kode] : []) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition {{ $filterStatus === $kode ? 'bg-[#133A26] text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <a href="{{ route('pengguna.setoran.create') }}" class="px-3.5 py-1.5 rounded-xl bg-[#1D6B45] text-white text-xs font-semibold">
            + Buat Manifes Baru
        </a>
    </section>

    {{-- Daftar Riwayat Manifes --}}
    <section class="space-y-4">
        @forelse ($daftarRiwayat as $s)
            @php
                $adaMedis = $s->detail->contains(fn ($d) => $d->jenisSampah?->kategori === 'medis');
                $bisaUlas = $s->status === 'selesai';
                $bisaBatal = in_array($s->status, ['menunggu_kurir', 'menuju_tps'], true);
            @endphp

            <article class="bg-white border border-stone-200 rounded-xl p-5 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b border-stone-200">
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="font-judul font-bold text-base text-[#1F2421] angka-tabular">{{ $s->kode_setoran }}</h2>
                            @if ($adaMedis)
                                <span class="px-2 py-0.5 rounded bg-[#FCE8E6] text-[#B3261E] font-bold text-[11px] uppercase">
                                    Limbah Medis
                                </span>
                            @endif
                            <span class="px-2.5 py-0.5 rounded text-xs font-bold uppercase {{ $s->status === 'selesai' ? 'bg-emerald-100 text-emerald-900' : ($s->status === 'ditolak_tps' || $s->status === 'dibatalkan' ? 'bg-red-100 text-red-900' : 'bg-amber-100 text-amber-900') }}">
                                {{ str_replace('_', ' ', $s->status) }}
                            </span>
                        </div>
                        <p class="text-xs text-stone-500 mt-1">
                            Fasilitas Tujuan: <strong class="text-stone-800">{{ $s->tps?->nama_tps ?? '-' }}</strong>
                            · Metode: <strong>{{ $s->metode === 'dijemput' ? 'Dijemput Kurir' : 'Antar Sendiri' }}</strong>
                            @if ($s->penjemputan?->kurirUser)
                                · Kurir: <strong>{{ $s->penjemputan->kurirUser->nama }}</strong>
                            @endif
                        </p>
                    </div>

                    <div class="text-right text-xs angka-tabular">
                        <div class="text-stone-500">Dibuat: {{ $s->created_at?->format('d/m/Y H:i') }}</div>
                        @if ($s->selesai_at)
                            <div class="text-emerald-700 font-semibold">Selesai: {{ $s->selesai_at->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>
                </div>

                @if ($s->alasan_penolakan)
                    <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-900">
                        <strong>Alasan Penolakan Fasilitas:</strong> {{ $s->alasan_penolakan }}
                    </div>
                @endif

                {{-- Tabel Rincian Jumlah Item Estimasi vs Aktual --}}
                <div class="border border-stone-200 rounded-lg overflow-hidden">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-stone-50 border-b border-stone-200 font-semibold text-stone-600">
                                <th class="py-2 px-3">Jenis Limbah / Sampah</th>
                                <th class="py-2 px-3">Kategori</th>
                                <th class="py-2 px-3 text-right">Jumlah Estimasi</th>
                                <th class="py-2 px-3 text-right">Jumlah Aktual Diterima</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-200 angka-tabular">
                            @foreach ($s->detail as $d)
                                <tr>
                                    <td class="py-2 px-3 font-semibold text-[#1F2421]">{{ $d->jenisSampah?->nama ?? '-' }}</td>
                                    <td class="py-2 px-3 uppercase text-stone-500">{{ $d->jenisSampah?->kategori }}</td>
                                    <td class="py-2 px-3 text-right text-stone-600">{{ number_format($d->berat_estimasi_kg, 0, ',', '.') }} item</td>
                                    <td class="py-2 px-3 text-right font-bold text-[#1F2421]">
                                        @if ($d->berat_aktual_kg !== null)
                                            {{ number_format($d->berat_aktual_kg, 0, ',', '.') }} item
                                        @else
                                            <span class="text-stone-400 font-normal">Belum diverifikasi</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        @if ($s->total_berat_aktual_kg !== null)
                            <tfoot>
                                <tr class="bg-emerald-50/50 border-t border-stone-200 font-bold angka-tabular">
                                    <td colspan="2" class="py-2 px-3 text-[#1F2421]">Total Item Diterima &amp; Poin Terbit</td>
                                    <td class="py-2 px-3 text-right text-[#1F2421]">{{ number_format($s->total_berat_aktual_kg, 0, ',', '.') }} item</td>
                                    <td class="py-2 px-3 text-right text-[#1D6B45]">+{{ $s->total_poin }} poin</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                {{-- Jejak Log Perubahan Status (setoran_status_log) --}}
                @if ($s->statusLog->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-stone-500 angka-tabular">
                        <span class="font-semibold text-stone-600">Jejak Status:</span>
                        @foreach ($s->statusLog as $log)
                            <span class="px-2 py-0.5 rounded bg-stone-100 border border-stone-200">
                                {{ str_replace('_', ' ', $log->status_baru) }} ({{ $log->created_at?->format('d/m H:i') }})
                            </span>
                        @endforeach
                    </div>
                @endif

                {{-- Bagian Ulasan & Rating (Jika Selesai) --}}
                @if ($bisaUlas)
                    <div class="pt-3 border-t border-stone-200">
                        @if ($s->ulasan)
                            <div class="p-3.5 rounded-xl bg-amber-50/60 border border-amber-200 text-xs flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="font-bold text-amber-950">Ulasan Anda:</span>
                                    <span class="ml-2 font-semibold text-amber-700">Rating Fasilitas: ★ {{ $s->ulasan->rating_tps }}/5</span>
                                    @if ($s->ulasan->rating_kurir)
                                        <span class="ml-2 font-semibold text-amber-700">· Rating Kurir: ★ {{ $s->ulasan->rating_kurir }}/5</span>
                                    @endif
                                    @if ($s->ulasan->komentar)
                                        <p class="text-stone-700 italic mt-1">"{{ $s->ulasan->komentar }}"</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <details class="text-xs">
                                <summary class="font-semibold text-[#1D6B45] cursor-pointer hover:underline">
                                    ★ Beri Rating &amp; Ulasan Pelayanan untuk Manifes Ini
                                </summary>
                                <form method="POST" action="{{ route('pengguna.setoran.ulasan', $s) }}" class="mt-3 p-4 rounded-xl bg-stone-50 border border-stone-200 space-y-3">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block font-semibold text-stone-700 mb-1">Rating Fasilitas ({{ $s->tps?->nama_tps }})</label>
                                            <select name="rating_tps" required class="w-full px-3 py-1.5 rounded-lg border border-stone-300 bg-white">
                                                @for ($r = 5; $r >= 1; $r--)
                                                    <option value="{{ $r }}">{{ $r }} Bintang</option>
                                                @endfor
                                            </select>
                                        </div>

                                        @if ($s->metode === 'dijemput')
                                            <div>
                                                <label class="block font-semibold text-stone-700 mb-1">Rating Kurir Penjemput</label>
                                                <select name="rating_kurir" class="w-full px-3 py-1.5 rounded-lg border border-stone-300 bg-white">
                                                    @for ($r = 5; $r >= 1; $r--)
                                                        <option value="{{ $r }}">{{ $r }} Bintang</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        @endif

                                        <div class="{{ $s->metode === 'dijemput' ? '' : 'sm:col-span-2' }}">
                                            <label class="block font-semibold text-stone-700 mb-1">Komentar Ulasan (Opsional)</label>
                                            <input type="text" name="komentar" maxlength="255" placeholder="Pelayanan cepat, timbangan akurat…"
                                                   class="w-full px-3 py-1.5 rounded-lg border border-stone-300 bg-white">
                                        </div>
                                    </div>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white font-semibold cursor-pointer">
                                        Kirim Ulasan
                                    </button>
                                </form>
                            </details>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="bg-white border border-stone-200 rounded-xl p-8 text-center text-sm text-stone-500">
                Belum ada riwayat manifes setoran yang sesuai dengan filter ini.
            </div>
        @endforelse

        @if ($daftarRiwayat->hasPages())
            <div class="pt-2">
                {{ $daftarRiwayat->links() }}
            </div>
        @endif
    </section>

@endsection

