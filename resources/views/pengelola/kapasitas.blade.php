@extends('layouts.pengelola')

@section('title', 'Kuota Simpan Medis & B3')
@section('heading', $tps ? 'Ruang Simpan & Kuota Limbah · '.$tps->nama_tps : 'Kapasitas Fasilitas')
@section('subheading', 'Atur batas daya tampung aman per klasifikasi limbah (Medis Infeksius, Benda Tajam, Farmasi, B3) dan kosongkan saat diangkut ke pengolahan akhir.')

@section('content')

    @if (! $tps)
        <section class="bg-white border border-stone-200 rounded-xl p-6">
            <p class="text-sm text-stone-600">Silakan daftarkan lokasi TPS terlebih dahulu di menu <a href="{{ route('pengelola.profil-tps') }}" class="font-semibold text-[#1D6B45] underline">Operasional & Lokasi TPS</a>.</p>
        </section>
    @else

        <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-200 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-bold text-[#1F2421]">Pengaturan Petak Jenis Sampah</h2>
                    <p class="text-xs text-stone-500">
                        Total terpakai saat ini: <strong class="text-[#1F2421] angka-tabular">{{ number_format($tps->kapasitas_terpakai_kg, 1, ',', '.') }} kg</strong>
                        dari kapasitas TPS <strong class="text-[#1F2421] angka-tabular">{{ number_format($tps->kapasitas_total_kg, 0, ',', '.') }} kg</strong>.
                    </p>
                </div>

                @if (! $tps->izin_limbah_medis)
                    <span class="px-2.5 py-1 text-xs font-medium rounded bg-stone-100 text-stone-600">
                        TPS belum berizin limbah medis (kategori medis dikunci)
                    </span>
                @else
                    <span class="px-2.5 py-1 text-xs font-semibold rounded bg-red-100 text-red-900">
                        Berizin Limbah Medis Aktif
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-xs font-semibold text-stone-600">
                            <th class="py-3 px-4">Jenis Sampah</th>
                            <th class="py-3 px-4">Kategori & Poin</th>
                            <th class="py-3 px-4">Diterima di TPS</th>
                            <th class="py-3 px-4 w-40">Muatan Saat Ini (kg)</th>
                            <th class="py-3 px-4 w-40">Batas Maksimum (kg)</th>
                            <th class="py-3 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach ($semuaJenis as $j)
                            @php
                                $terkunciMedis = $j->kategori === 'medis' && ! $tps->izin_limbah_medis;
                                $pivot = $pivotMap->get($j->jenis_id)?->pivot;
                                $diterima = $pivot ? (bool) $pivot->diterima : false;
                                $terpakai = $pivot ? (float) $pivot->terpakai_kg : 0;
                                $maks = $pivot ? (float) $pivot->kapasitas_maks_kg : 200;
                                $formId = 'form-kap-'.$j->jenis_id;

                                $warnaKat = match ($j->kategori) {
                                    'organik' => 'bg-emerald-100 text-emerald-900',
                                    'anorganik' => 'bg-amber-100 text-amber-900',
                                    'elektronik' => 'bg-sky-100 text-sky-900',
                                    'b3' => 'bg-red-100 text-red-900',
                                    'medis' => 'bg-rose-100 text-rose-900',
                                    default => 'bg-stone-100 text-stone-800',
                                };
                            @endphp
                            <tr class="{{ $terkunciMedis ? 'bg-stone-50 opacity-60' : 'hover:bg-stone-50/70' }}">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-[#1F2421]">{{ $j->nama }}</div>
                                    <div class="text-xs text-stone-500">{{ $j->deskripsi }}</div>
                                </td>
                                <td class="py-3 px-4 angka-tabular">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded uppercase {{ $warnaKat }}">
                                        {{ $j->kategori }}
                                    </span>
                                    <span class="ml-1.5 text-xs text-stone-500">{{ $j->poin_per_kg }} pts/kg</span>
                                </td>
                                <td class="py-3 px-4">
                                    @if ($terkunciMedis)
                                        <span class="text-xs text-stone-500">Perlu Izin Medis</span>
                                    @else
                                        <label class="inline-flex items-center gap-2 text-xs font-semibold cursor-pointer">
                                            <input form="{{ $formId }}" type="checkbox" name="diterima" value="1"
                                                   @checked($diterima)
                                                   class="w-4 h-4 rounded accent-[#1D6B45]">
                                            <span class="{{ $diterima ? 'text-[#1D6B45]' : 'text-stone-500' }}">
                                                {{ $diterima ? 'Diterima' : 'Tidak diterima' }}
                                            </span>
                                        </label>
                                    @endif
                                </td>
                                <td class="py-3 px-4 angka-tabular">
                                    <div class="flex items-center gap-1.5">
                                        <input form="{{ $formId }}" type="number" step="0.1" min="0" name="terpakai_kg"
                                               value="{{ $terpakai }}" @disabled($terkunciMedis)
                                               aria-label="Muatan terpakai kg {{ $j->nama }}"
                                               class="w-24 px-2.5 py-1 rounded border border-stone-300 bg-white text-sm font-semibold text-right">
                                        <span class="text-xs text-stone-500">kg</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 angka-tabular">
                                    <div class="flex items-center gap-1.5">
                                        <input form="{{ $formId }}" type="number" step="1" min="0" name="kapasitas_maks_kg"
                                               value="{{ $maks }}" @disabled($terkunciMedis)
                                               aria-label="Kapasitas maksimum kg {{ $j->nama }}"
                                               class="w-24 px-2.5 py-1 rounded border border-stone-300 bg-white text-sm font-semibold text-right">
                                        <span class="text-xs text-stone-500">kg</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    @if (! $terkunciMedis)
                                        <div class="inline-flex items-center justify-end gap-1.5">
                                            <form id="{{ $formId }}" method="POST" action="{{ route('pengelola.kapasitas.update', $j) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-900 text-white text-xs font-semibold cursor-pointer">
                                                    Simpan
                                                </button>
                                            </form>

                                            @if ($terpakai > 0)
                                                <form method="POST" action="{{ route('pengelola.kapasitas.kosongkan', $j) }}">
                                                    @csrf
                                                    <button type="submit" title="Reset muatan ke 0 kg setelah diangkut ke TPA"
                                                            class="px-2.5 py-1.5 rounded-lg border border-emerald-400 text-emerald-800 hover:bg-emerald-50 text-xs font-semibold cursor-pointer">
                                                        Kosongkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

    @endif

@endsection

