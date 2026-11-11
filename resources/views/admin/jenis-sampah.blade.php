@extends('layouts.admin')

@section('title', 'Klasifikasi Limbah Medis, B3 & Poin')
@section('heading', 'Master Klasifikasi Limbah Medis, B3 & Poin')
@section('subheading', 'Atur protokol penanganan khusus (Medis Infeksius, Benda Tajam, Farmasi, B3) dan tarif poin insentif per item.')

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Tabel Master Jenis Sampah (2 Kolom) --}}
        <section class="lg:col-span-2 bg-white border border-stone-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-[#1F2421]">Daftar Jenis Sampah ({{ $daftarJenis->count() }})</h2>
                    <p class="text-xs text-stone-500">Ubah nilai poin langsung pada baris jenis sampah lalu klik Simpan.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-stone-200 bg-stone-50 text-xs font-semibold text-stone-600">
                            <th class="py-3 px-4">Jenis & Deskripsi</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Penanganan Khusus</th>
                            <th class="py-3 px-4 w-36">Poin / Item</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach ($daftarJenis as $j)
                            @php
                                $warnaKat = match ($j->kategori) {
                                    'organik' => 'bg-emerald-100 text-emerald-900',
                                    'anorganik' => 'bg-amber-100 text-amber-900',
                                    'elektronik' => 'bg-sky-100 text-sky-900',
                                    'b3' => 'bg-red-100 text-red-900',
                                    'medis' => 'bg-rose-100 text-rose-900',
                                    default => 'bg-stone-100 text-stone-800',
                                };
                                $formId = 'form-jenis-'.$j->jenis_id;
                            @endphp
                            <tr class="hover:bg-stone-50/70">
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-[#1F2421]">{{ $j->nama }}</div>
                                    <input form="{{ $formId }}" type="text" name="deskripsi" value="{{ $j->deskripsi }}"
                                           class="mt-1 w-full px-2 py-1 text-xs text-stone-600 rounded border border-transparent hover:border-stone-300 focus:border-stone-400 bg-transparent focus:bg-white">
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded uppercase {{ $warnaKat }}">
                                        {{ $j->kategori }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <label class="inline-flex items-center gap-1.5 text-xs text-stone-700 cursor-pointer">
                                        <input form="{{ $formId }}" type="checkbox" name="perlu_penanganan_khusus" value="1"
                                               @checked($j->perlu_penanganan_khusus)
                                               class="w-4 h-4 rounded accent-[#1D6B45]">
                                        <span>{{ $j->perlu_penanganan_khusus ? 'Wajib khusus' : 'Standar' }}</span>
                                    </label>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5 angka-tabular">
                                        <input form="{{ $formId }}" type="number" name="poin_per_kg" min="0" max="10000"
                                               value="{{ $j->poin_per_kg }}" aria-label="Poin per kg untuk {{ $j->nama }}"
                                               class="w-20 px-2.5 py-1 rounded border border-stone-300 bg-white text-sm font-semibold text-right">
                                        <span class="text-xs text-stone-500">pts</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form id="{{ $formId }}" method="POST" action="{{ route('admin.jenis-sampah.update', $j) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-stone-800 hover:bg-stone-900 text-white text-xs font-semibold cursor-pointer">
                                            Simpan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Form Tambah Jenis Sampah --}}
        <section class="bg-white border border-stone-200 rounded-xl p-5">
            <h2 class="text-base font-bold text-[#1F2421]">Tambah Jenis Sampah</h2>
            <p class="text-xs text-stone-500 mt-0.5 mb-4">Tambahkan klasifikasi sampah baru ke katalog penerimaan TPS.</p>

            <form method="POST" action="{{ route('admin.jenis-sampah.store') }}" class="space-y-3.5 text-sm">
                @csrf

                <div>
                    <label for="nama" class="block text-xs font-semibold text-stone-700 mb-1">Nama Jenis Sampah</label>
                    <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required maxlength="80"
                           placeholder="Mis. Minyak jelantah"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                </div>

                <div>
                    <label for="kategori" class="block text-xs font-semibold text-stone-700 mb-1">Kategori Limbah</label>
                    <select id="kategori" name="kategori" required
                            class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                        <option value="medis" selected>Medis (Khusus Fasilitas Berizin)</option>
                    </select>
                </div>

                <div>
                    <label for="poin_per_kg" class="block text-xs font-semibold text-stone-700 mb-1">Poin per Item</label>
                    <input id="poin_per_kg" name="poin_per_kg" type="number" min="0" max="10000" value="{{ old('poin_per_kg', 10) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm angka-tabular">
                    <p class="text-xs text-stone-500 mt-1">Isi 0 untuk limbah medis yang tidak diberi insentif poin.</p>
                </div>

                <div>
                    <label for="deskripsi" class="block text-xs font-semibold text-stone-700 mb-1">Deskripsi Contoh</label>
                    <input id="deskripsi" name="deskripsi" type="text" value="{{ old('deskripsi') }}" maxlength="255"
                           placeholder="Contoh barang yang termasuk jenis ini"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                </div>

                <label class="flex items-center gap-2 pt-1 text-xs font-medium text-stone-700 cursor-pointer">
                    <input type="checkbox" name="perlu_penanganan_khusus" value="1" @checked(old('perlu_penanganan_khusus'))
                           class="w-4 h-4 rounded accent-[#1D6B45]">
                    <span>Memerlukan wadah / penanganan khusus saat dijemput</span>
                </label>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white font-semibold text-sm cursor-pointer">
                    Tambahkan Jenis Sampah
                </button>
            </form>
        </section>
    </div>

@endsection

