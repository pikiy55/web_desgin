@extends('layouts.pengelola')

@section('title', 'Riwayat Manifes & Ulasan')
@section('heading', $tps ? 'Riwayat Manifes · '.$tps->nama_tps : 'Riwayat Manifes Setoran')
@section('subheading', 'Daftar manifes limbah medis, B3, dan daur ulang yang telah ditimbang beserta ulasan layanan.')

@section('header_actions')
    @if ($rataRatingTps !== null)
        <div class="px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-300 text-xs font-semibold text-amber-950 angka-tabular">
            Rating TPS: ★ {{ number_format((float) $rataRatingTps, 1, ',', '.') }} / 5,0
        </div>
    @endif
@endsection

@section('content')

    @if (! $tps)
        <section class="bg-white border border-stone-200 rounded-xl p-6">
            <p class="text-sm text-stone-600">Pilih atau daftarkan lokasi TPS terlebih dahulu.</p>
        </section>
    @else

        {{-- Filter Status --}}
        <section class="bg-white border border-stone-200 rounded-xl p-4">
            <form method="GET" action="{{ route('pengelola.riwayat') }}" class="flex flex-wrap items-center gap-3">
                <label for="status" class="text-xs font-semibold text-stone-700">Filter Status:</label>
                <select id="status" name="status" onchange="this.form.submit()"
                        class="px-3 py-1.5 rounded-lg border border-stone-300 text-sm bg-white">
                    <option value="">Semua Status</option>
                    <option value="selesai" @selected(request('status') === 'selesai')>Selesai Ditimbang</option>
                    <option value="tiba_di_tps" @selected(request('status') === 'tiba_di_tps')>Tiba di TPS</option>
                    <option value="menuju_tps" @selected(request('status') === 'menuju_tps')>Menuju TPS</option>
                    <option value="diangkut" @selected(request('status') === 'diangkut')>Diangkut Kurir</option>
                    <option value="ditolak_tps" @selected(request('status') === 'ditolak_tps')>Ditolak TPS</option>
                </select>
            </form>
        </section>

        {{-- Tabel Riwayat Setoran --}}
        <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-xs font-semibold text-stone-600">
                            <th class="py-3 px-4">Kode & Waktu</th>
                            <th class="py-3 px-4">Warga & Pengantar</th>
                            <th class="py-3 px-4">Rincian Sampah</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Total Berat / Poin</th>
                            <th class="py-3 px-4">Ulasan Warga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @forelse ($riwayatSetoran as $s)
                            <tr class="hover:bg-stone-50/70">
                                <td class="py-3.5 px-4 angka-tabular">
                                    <div class="font-bold text-xs text-[#1F2421]">{{ $s->kode_setoran }}</div>
                                    <div class="text-xs text-stone-500">{{ $s->created_at?->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="font-semibold text-[#1F2421]">{{ $s->pengguna?->nama ?? '-' }}</div>
                                    <div class="text-stone-500">
                                        {{ $s->metode === 'dijemput' ? 'Kurir: '.($s->penjemputan?->kurirUser?->nama ?? '-') : 'Antar sendiri' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-stone-700 angka-tabular">
                                    @foreach ($s->detail as $d)
                                        <div>
                                            {{ $d->jenisSampah?->nama }}:
                                            <strong>{{ number_format($d->berat_aktual_kg ?? $d->berat_estimasi_kg, 0, ',', '.') }} item</strong>
                                        </div>
                                    @endforeach
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $badge = match ($s->status) {
                                            'selesai' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                                            'ditolak_tps', 'dibatalkan' => 'bg-red-50 text-red-800 border-red-300',
                                            default => 'bg-amber-50 text-amber-800 border-amber-300',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 text-xs font-bold uppercase rounded border {{ $badge }}">
                                        {{ str_replace('_', ' ', $s->status) }}
                                    </span>
                                    @if ($s->alasan_penolakan)
                                        <div class="text-xs text-red-700 mt-1">{{ $s->alasan_penolakan }}</div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right angka-tabular text-xs">
                                    @if ($s->total_berat_aktual_kg !== null)
                                        <div class="font-bold text-[#1F2421]">{{ number_format($s->total_berat_aktual_kg, 0, ',', '.') }} item</div>
                                        <div class="font-semibold text-[#1D6B45]">+{{ $s->total_poin }} pts</div>
                                    @else
                                        <span class="text-stone-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if ($s->ulasan && $s->ulasan->rating_tps)
                                        <div class="font-bold text-amber-700 angka-tabular">★ {{ $s->ulasan->rating_tps }} / 5</div>
                                        @if ($s->ulasan->komentar)
                                            <div class="text-stone-600 mt-0.5">"{{ $s->ulasan->komentar }}"</div>
                                        @endif
                                    @else
                                        <span class="text-stone-400">Belum ada ulasan</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-sm text-stone-500">
                                    Belum ada riwayat setoran untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($riwayatSetoran instanceof \Illuminate\Contracts\Pagination\Paginator && $riwayatSetoran->hasPages())
                <div class="px-4 py-3 border-t border-stone-200">
                    {{ $riwayatSetoran->links() }}
                </div>
            @endif
        </section>

    @endif

@endsection

