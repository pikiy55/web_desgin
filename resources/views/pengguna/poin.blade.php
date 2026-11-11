@extends('layouts.pengguna')

@section('title', 'Dompet Poin & Tukar K3')
@section('heading', 'Dompet Poin Insentif B3 & Penukaran K3')
@section('subheading', 'Kumpulkan poin dari penyetoran limbah B3, elektronik, dan daur ulang, lalu tukarkan dengan Safety Box atau perlengkapan K3.')

@section('content')

    {{-- Ringkasan Saldo Poin --}}
    <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-stone-200">
            <div class="p-5">
                <div class="text-xs text-stone-500 font-medium">Saldo Poin Aktif</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold angka-tabular text-[#1D6B45]">
                        {{ number_format($saldoPoin, 0, ',', '.') }}
                    </span>
                    <span class="text-xs font-semibold text-stone-500">poin siap tukar</span>
                </div>
            </div>

            <div class="p-5">
                <div class="text-xs text-stone-500 font-medium">Total Poin Masuk (Dari Setoran)</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-3xl font-bold angka-tabular text-[#1F2421]">
                        +{{ number_format($totalMasuk, 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-stone-500">poin terkumpul</span>
                </div>
            </div>

            <div class="p-5">
                <div class="text-xs text-stone-500 font-medium">Total Poin Ditukarkan</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-3xl font-bold angka-tabular text-stone-700">
                        {{ number_format($totalKeluar, 0, ',', '.') }}
                    </span>
                    <span class="text-xs text-stone-500">poin terpakai</span>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- KOLOM KIRI (7 Kolom): KATALOG PENUKARAN PERLENGKAPAN K3 & SAFETY BOX --}}
        <section class="lg:col-span-7 space-y-4">
            <div>
                <h2 class="font-judul font-bold text-base text-[#1F2421]">Katalog Penukaran Perlengkapan K3 &amp; Wadah Medis</h2>
                <p class="text-xs text-stone-500">Tukarkan poin insentif Anda dengan wadah keselamatan limbah medis atau perlengkapan pemilahan B3.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach ($katalogPenukaran as $item)
                    @php
                        $cukupPoin = $saldoPoin >= $item['poin'];
                    @endphp
                    <article class="bg-white border border-stone-200 rounded-xl p-5 flex flex-col justify-between gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2 py-0.5 rounded bg-stone-100 text-stone-600 font-mono text-[11px]">
                                    {{ $item['kode'] }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-lg bg-[#133A26] text-[#F2C230] font-judul font-bold text-xs angka-tabular">
                                    {{ $item['poin'] }} poin
                                </span>
                            </div>
                            <h3 class="font-judul font-bold text-sm text-[#1F2421]">
                                {{ $item['nama'] }}
                            </h3>
                            <p class="text-xs text-stone-600 leading-relaxed">
                                {{ $item['deskripsi'] }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('pengguna.poin.tukar') }}" onsubmit="return confirm('Tukarkan {{ $item['poin'] }} poin dengan {{ $item['nama'] }}?')">
                            @csrf
                            <input type="hidden" name="nama_item" value="{{ $item['nama'] }}">
                            <input type="hidden" name="jumlah" value="{{ $item['poin'] }}">
                            <button type="submit"
                                    {{ ! $cukupPoin ? 'disabled' : '' }}
                                    class="w-full py-2 px-3 rounded-lg text-xs font-semibold transition {{ $cukupPoin ? 'bg-[#1D6B45] hover:bg-[#13502F] text-white cursor-pointer' : 'bg-stone-100 text-stone-400 cursor-not-allowed' }}">
                                {{ $cukupPoin ? 'Tukar dengan '.$item['poin'].' Poin' : 'Poin Belum Cukup (Kurang '.($item['poin'] - $saldoPoin).' pts)' }}
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- KOLOM KANAN (5 Kolom): RIWAYAT TRANSAKSI POIN --}}
        <section class="lg:col-span-5 bg-white border border-stone-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-200">
                <h2 class="font-judul font-bold text-base text-[#1F2421]">Mutasi &amp; Riwayat Transaksi Poin</h2>
                <p class="text-xs text-stone-500">Pencatatan otomatis dari tabel <code class="text-[11px]">transaksi_poin</code>.</p>
            </div>

            @if ($riwayatPoin->isEmpty())
                <div class="p-6 text-xs text-stone-500">
                    Belum ada transaksi poin yang tercatat.
                </div>
            @else
                <ul class="divide-y divide-stone-200 text-xs">
                    @foreach ($riwayatPoin as $tp)
                        <li class="p-4 flex items-center justify-between gap-3 hover:bg-stone-50/70">
                            <div class="min-w-0">
                                <div class="font-semibold text-[#1F2421]">{{ $tp->keterangan ?: 'Transaksi Poin' }}</div>
                                <div class="text-stone-500 mt-0.5 angka-tabular">
                                    {{ $tp->created_at?->format('d/m/Y H:i') }}
                                    @if ($tp->setoran)
                                        · {{ $tp->setoran->kode_setoran }}
                                    @endif
                                </div>
                            </div>
                            <span class="font-judul font-extrabold text-sm angka-tabular shrink-0 {{ $tp->tipe === 'masuk' ? 'text-[#1D6B45]' : 'text-[#B3261E]' }}">
                                {{ $tp->tipe === 'masuk' ? '+' : '−' }}{{ number_format($tp->jumlah, 0, ',', '.') }} pts
                            </span>
                        </li>
                    @endforeach
                </ul>

                @if ($riwayatPoin->hasPages())
                    <div class="p-3 border-t border-stone-200">
                        {{ $riwayatPoin->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>

@endsection

