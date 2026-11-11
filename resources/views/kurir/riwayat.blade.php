@extends('layouts.kurir')

@section('title', 'Riwayat Manifes Pengantaran')
@section('heading', 'Riwayat Manifes Pengangkutan Limbah Medis & B3')
@section('subheading', 'Daftar manifes penjemputan limbah medis, B3, dan daur ulang yang telah diserahkan ke fasilitas berizin.')

@section('content')

    {{-- Kartu Ringkasan Riwayat --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-xl border border-stone-200">
            <div class="text-xs text-stone-500 font-medium">Total Pengantaran Sukses</div>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-[#1F2421] angka-tabular">{{ $totalSelesai }}</span>
                <span class="text-xs text-stone-500">tugas selesai</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-stone-200">
            <div class="text-xs text-stone-500 font-medium">Total Sampah Telah Diangkut</div>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-[#175E54] angka-tabular">
                    {{ number_format($totalKgDiangkut, 1, ',', '.') }}
                </span>
                <span class="text-xs text-stone-500">kg sampah</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-stone-200">
            <div class="text-xs text-stone-500 font-medium">Rating Kepuasan Warga</div>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-3xl font-bold text-amber-500 angka-tabular">
                    ⭐ {{ number_format($profil->rating_rata > 0 ? $profil->rating_rata : 5.0, 1) }}
                </span>
                <span class="text-xs text-stone-500">/ 5.0 bintang</span>
            </div>
        </div>
    </div>

    {{-- Tabel Riwayat Lengkap --}}
    <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-200 flex items-center justify-between">
            <h2 class="font-judul font-bold text-base text-[#1F2421]">Daftar Tugas Pengantaran Selesai</h2>
            <span class="text-xs text-stone-500 angka-tabular">Menampilkan halaman {{ $daftarRiwayat->currentPage() }} dari {{ $daftarRiwayat->lastPage() }}</span>
        </div>

        @if ($daftarRiwayat->isEmpty())
            <div class="p-12 text-center space-y-2">
                <svg class="w-12 h-12 text-stone-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <div class="text-base font-semibold text-[#1F2421]">Belum ada riwayat pengantaran</div>
                <p class="text-xs text-stone-500 max-w-sm mx-auto">
                    Setiap tugas penjemputan yang telah Anda serahkan di pos TPS akan tercatat lengkap di halaman ini.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-xs font-semibold text-stone-600">
                            <th class="py-3 px-4">Kode & Tanggal</th>
                            <th class="py-3 px-4">Penyetor (Warga)</th>
                            <th class="py-3 px-4">TPS Tujuan</th>
                            <th class="py-3 px-4 text-right">Berat Muatan</th>
                            <th class="py-3 px-4 text-center">Status TPS</th>
                            <th class="py-3 px-4">Ulasan Warga</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 text-xs angka-tabular">
                        @foreach ($daftarRiwayat as $item)
                            @php
                                $s = $item->setoran;
                                $beratFinal = (float) ($s->total_berat_aktual_kg ?: $s->detail->sum('berat_estimasi_kg'));
                                $ulasan = $s->ulasan;
                            @endphp
                            <tr class="hover:bg-stone-50/60 transition">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-[#1F2421] text-xs">{{ $s->kode_setoran }}</div>
                                    <div class="text-stone-500 text-[11px] mt-0.5">
                                        {{ $item->tiba_at?->format('d/m/Y H:i') ?? '-' }} WIB
                                    </div>
                                    @if ($item->foto_bukti_serah || $item->foto_bukti_ambil)
                                        <div class="mt-1 flex gap-1">
                                            @if ($item->foto_bukti_ambil)
                                                <a href="{{ asset($item->foto_bukti_ambil) }}" target="_blank"
                                                   class="text-[10px] text-teal-700 bg-teal-50 px-1.5 py-0.5 rounded border border-teal-200 hover:underline">
                                                    Bukti Jemput
                                                </a>
                                            @endif
                                            @if ($item->foto_bukti_serah)
                                                <a href="{{ asset($item->foto_bukti_serah) }}" target="_blank"
                                                   class="text-[10px] text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200 hover:underline">
                                                    Bukti TPS
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3 px-4">
                                    <div class="font-semibold text-[#1F2421]">{{ $s->pengguna?->nama ?? '-' }}</div>
                                    <div class="text-stone-500 max-w-xs truncate text-[11px]">
                                        {{ $item->alamat?->alamat_lengkap ?? '-' }}
                                    </div>
                                </td>

                                <td class="py-3 px-4">
                                    <div class="font-semibold text-[#1F2421]">{{ $s->tps?->nama_tps ?? '-' }}</div>
                                    <div class="text-stone-500 text-[11px] truncate max-w-xs">{{ $s->tps?->alamat }}</div>
                                </td>

                                <td class="py-3 px-4 text-right">
                                    <div class="font-bold text-[#175E54] text-xs">
                                        {{ number_format($beratFinal, 2, ',', '.') }} kg
                                    </div>
                                    <div class="text-stone-500 text-[11px]">
                                        {{ $s->detail->count() }} jenis sampah
                                    </div>
                                </td>

                                <td class="py-3 px-4 text-center">
                                    @if ($s->status === 'selesai')
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                            Selesai Ditimbang
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-blue-100 text-blue-800">
                                            Tiba di TPS
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3 px-4">
                                    @if ($ulasan && $ulasan->rating_kurir)
                                        <div class="flex items-center gap-1 text-amber-500 font-bold">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <span>{{ $i <= $ulasan->rating_kurir ? '★' : '☆' }}</span>
                                            @endfor
                                            <span class="text-stone-600 font-normal ml-1">({{ $ulasan->rating_kurir }}/5)</span>
                                        </div>
                                        @if ($ulasan->komentar)
                                            <div class="text-stone-600 italic text-[11px] mt-0.5 line-clamp-2">
                                                "{{ $ulasan->komentar }}"
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-stone-400 italic text-[11px]">Belum ada ulasan</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if ($daftarRiwayat->hasPages())
                <div class="px-5 py-4 border-t border-stone-200">
                    {{ $daftarRiwayat->links() }}
                </div>
            @endif
        @endif
    </section>

    {{-- Bagian Ulasan & Testimoni Warga --}}
    @if ($ulasanWarga->isNotEmpty())
        <section class="bg-white border border-stone-200 rounded-xl p-5 space-y-4">
            <h2 class="font-judul font-bold text-base text-[#1F2421]">Ulasan & Penilaian dari Warga</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($ulasanWarga as $u)
                    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-[#1F2421]">{{ $u->pengguna?->nama ?? 'Warga' }}</span>
                            <div class="text-amber-500 font-bold text-sm">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span>{{ $i <= $u->rating_kurir ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                        </div>

                        @if ($u->komentar)
                            <p class="text-stone-700 italic">"{{ $u->komentar }}"</p>
                        @endif

                        <div class="text-stone-400 text-[11px] pt-1 border-t border-stone-200 flex items-center justify-between">
                            <span>Setoran: {{ $u->setoran?->kode_setoran }}</span>
                            <span>{{ $u->created_at?->format('d M Y') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

@endsection

