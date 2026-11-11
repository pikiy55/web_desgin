@extends('layouts.admin')

@section('title', 'Protokol K3 & Edukasi Limbah Medis/B3')
@section('heading', 'Protokol K3, Panduan Kemasan Medis & Pengumuman')
@section('subheading', 'Kelola (Tambah, Lihat, Edit, Terbitkan, & Hapus) SOP pengemasan limbah medis, edukasi B3, dan pengumuman fasilitas berizin.')

@section('content')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Daftar Konten (2 Kolom) --}}
        <section class="lg:col-span-2 bg-white border border-stone-200 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-200 flex items-center justify-between gap-2">
                <h2 class="text-base font-bold text-[#1F2421]">Daftar Konten ({{ $daftarKonten->count() }})</h2>
                <span class="text-xs text-stone-500">Klik <strong>Lihat</strong> untuk baca isi, atau <strong>Edit / Hapus</strong> untuk mengelola.</span>
            </div>

            @if ($daftarKonten->isEmpty())
                <div class="p-6 text-sm text-stone-500">
                    Belum ada artikel atau pengumuman. Tulis konten pertama Anda melalui formulir di samping.
                </div>
            @else
                <div class="divide-y divide-stone-200">
                    @foreach ($daftarKonten as $k)
                        @php
                            $badgeStatus = match ($k->status) {
                                'terbit' => 'bg-emerald-100 text-emerald-900',
                                'arsip' => 'bg-stone-200 text-stone-700',
                                default => 'bg-amber-100 text-amber-900',
                            };
                            $tglInfo = $k->published_at
                                ? 'Terbit ' . $k->published_at->format('d/m/Y H:i')
                                : 'Dibuat ' . ($k->created_at?->format('d/m/Y') ?? '-');
                        @endphp
                        <article
                            data-konten-id="{{ $k->konten_id }}"
                            data-judul="{{ $k->judul }}"
                            data-jenis="{{ $k->jenis }}"
                            data-status="{{ $k->status }}"
                            data-ringkasan="{{ $k->ringkasan }}"
                            data-tanggal="{{ $tglInfo }}"
                            data-action-update="{{ route('admin.konten.update', $k->konten_id) }}"
                            class="p-4 flex flex-col sm:flex-row sm:items-start justify-between gap-4 hover:bg-stone-50/70 transition-colors">
                            <div class="min-w-0 space-y-1.5 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 text-xs font-bold uppercase rounded {{ $badgeStatus }}">
                                        {{ $k->status }}
                                    </span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-stone-100 text-stone-700 uppercase">
                                        {{ $k->jenis }}
                                    </span>
                                    <span class="text-xs text-stone-400 angka-tabular">
                                        {{ $tglInfo }}
                                    </span>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded bg-emerald-50 text-emerald-800 angka-tabular"
                                          title="{{ $k->punyaHalamanManual() ? 'Halaman diatur manual oleh admin' : 'Belum dibagi manual; dibagi otomatis di beranda' }}">
                                        {{ count($k->daftarHalaman()) }} halaman{{ $k->punyaHalamanManual() ? '' : ' (otomatis)' }}
                                    </span>
                                </div>

                                <h3 class="text-sm font-bold text-[#1F2421] cursor-pointer hover:text-[#1D6B45]"
                                    onclick="bukaModalLihatKonten({{ $k->konten_id }})">
                                    {{ $k->judul }}
                                </h3>

                                @if ($k->ringkasan)
                                    <p class="text-xs text-stone-600 line-clamp-2">{{ $k->ringkasan }}</p>
                                @else
                                    <p class="text-xs text-stone-500 line-clamp-2">{{ \Illuminate\Support\Str::limit($k->isiPolos(), 140) }}</p>
                                @endif

                                {{-- Daftar halaman buku (JSON) untuk modal Lihat & Edit --}}
                                <script type="application/json" id="halaman-data-{{ $k->konten_id }}">@json($k->daftarHalaman())</script>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5 shrink-0">
                                {{-- Tombol Lihat Isi (Read) --}}
                                <button type="button"
                                        onclick="bukaModalLihatKonten({{ $k->konten_id }})"
                                        class="px-2.5 py-1.5 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-stone-700 text-xs font-semibold cursor-pointer">
                                    Lihat
                                </button>

                                {{-- Tombol Edit (Update) --}}
                                <button type="button"
                                        onclick="bukaModalEditKonten({{ $k->konten_id }})"
                                        class="px-2.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold cursor-pointer">
                                    Edit
                                </button>

                                {{-- Tombol Cepat Terbitkan / Arsipkan --}}
                                @if ($k->status !== 'terbit')
                                    <form method="POST" action="{{ route('admin.konten.status', $k->konten_id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="terbit">
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs font-semibold cursor-pointer">
                                            Terbitkan
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.konten.status', $k->konten_id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="arsip">
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-stone-300 text-stone-700 hover:bg-stone-100 text-xs font-semibold cursor-pointer">
                                            Arsipkan
                                        </button>
                                    </form>
                                @endif

                                {{-- Tombol Hapus (Delete) --}}
                                <form method="POST"
                                      action="{{ route('admin.konten.destroy', $k->konten_id) }}"
                                      onsubmit="return confirm('Hapus konten &quot;{{ addslashes($k->judul) }}&quot; secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- Form Buat Konten Baru (Create) --}}
        <section class="bg-white border border-stone-200 rounded-xl p-5">
            <h2 class="text-base font-bold text-[#1F2421]">Tulis Konten Baru</h2>
            <p class="text-xs text-stone-500 mt-0.5 mb-4">Buat panduan pemilahan limbah medis atau pengumuman operasional fasilitas.</p>

            <form method="POST" action="{{ route('admin.konten.store') }}" class="space-y-3.5 text-sm">
                @csrf

                <div>
                    <label for="judul" class="block text-xs font-semibold text-stone-700 mb-1">Judul</label>
                    <input id="judul" name="judul" type="text" value="{{ old('judul') }}" required maxlength="200"
                           placeholder="Prosedur Pengemasan Safety Box & Kantong Kuning"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="jenis" class="block text-xs font-semibold text-stone-700 mb-1">Jenis Konten</label>
                        <select id="jenis" name="jenis" required
                                class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                            <option value="edukasi" @selected(old('jenis') === 'edukasi')>Edukasi</option>
                            <option value="pengumuman" @selected(old('jenis') === 'pengumuman')>Pengumuman</option>
                            <option value="artikel" @selected(old('jenis') === 'artikel')>Artikel</option>
                            <option value="banner" @selected(old('jenis') === 'banner')>Banner</option>
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-semibold text-stone-700 mb-1">Status Awal</label>
                        <select id="status" name="status" required
                                class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                            <option value="terbit" @selected(old('status', 'terbit') === 'terbit')>Langsung Terbit</option>
                            <option value="draft" @selected(old('status') === 'draft')>Simpan Draft</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="ringkasan" class="block text-xs font-semibold text-stone-700 mb-1">Ringkasan Singkat (Opsional)</label>
                    <input id="ringkasan" name="ringkasan" type="text" value="{{ old('ringkasan') }}" maxlength="300"
                           placeholder="Ditampilkan sebagai cuplikan kartu di halaman depan"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                </div>

                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700">Isi Buku per Halaman</label>
                            <p class="text-[11px] text-stone-500">Tambah, kurangi, atau ubah teks tiap halaman buku panduan.</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span id="info-jumlah-buat" class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 text-[11px] font-bold angka-tabular">1 Halaman</span>
                            <button type="button"
                                    onclick="pecahParagrafKeHalaman('daftar-halaman-buat')"
                                    title="Pecah paragraf panjang di halaman menjadi halaman-halaman terpisah"
                                    class="px-2 py-1 rounded-lg border border-stone-300 bg-stone-50 hover:bg-stone-100 text-stone-700 text-[11px] font-semibold cursor-pointer">
                                Bagi per Paragraf
                            </button>
                        </div>
                    </div>

                    @php
                        $halamanLama = old('halaman', [old('isi', '')]);
                        if (!is_array($halamanLama) || empty($halamanLama)) {
                            $halamanLama = [''];
                        }
                    @endphp
                    <div id="daftar-halaman-buat" data-label-counter="info-jumlah-buat" class="space-y-2.5 max-h-[360px] overflow-y-auto pr-1">
                        @foreach ($halamanLama as $idx => $teksHal)
                            <div data-item-halaman class="rounded-xl border border-stone-200 bg-stone-50/70 p-3 space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <span data-nomor-halaman class="text-xs font-bold text-[#133A26] uppercase tracking-wider">Halaman {{ $idx + 1 }}</span>
                                    <div class="flex items-center gap-1">
                                        <button type="button" onclick="geserHalaman(this, 'daftar-halaman-buat', -1)" title="Geser ke atas"
                                                class="px-2 py-0.5 rounded border border-stone-300 bg-white hover:bg-stone-100 text-stone-600 text-xs font-bold cursor-pointer">↑</button>
                                        <button type="button" onclick="geserHalaman(this, 'daftar-halaman-buat', 1)" title="Geser ke bawah"
                                                class="px-2 py-0.5 rounded border border-stone-300 bg-white hover:bg-stone-100 text-stone-600 text-xs font-bold cursor-pointer">↓</button>
                                        <button type="button" onclick="sisipkanHalamanSetelah(this, 'daftar-halaman-buat')" title="Sisipkan halaman baru di bawah ini"
                                                class="px-2 py-0.5 rounded border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[11px] font-semibold cursor-pointer">+ Sisip</button>
                                        <button type="button" data-btn-hapus onclick="hapusHalaman(this, 'daftar-halaman-buat')" title="Hapus halaman ini"
                                                class="px-2 py-0.5 rounded border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-[11px] font-semibold cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">Hapus</button>
                                    </div>
                                </div>
                                <textarea name="halaman[]" rows="4" required
                                          placeholder="Tulis isi teks untuk halaman ini…"
                                          class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm leading-relaxed">{{ $teksHal }}</textarea>
                            </div>
                        @endforeach
                    </div>

                    <button type="button"
                            onclick="tambahHalaman('daftar-halaman-buat', '')"
                            class="mt-2.5 w-full py-2 px-3 rounded-lg border-2 border-dashed border-emerald-600/40 hover:border-emerald-600 bg-emerald-50/40 hover:bg-emerald-50 text-[#1D6B45] font-semibold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition-colors">
                        <span>+ Tambah Halaman Baru</span>
                    </button>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white font-semibold text-sm cursor-pointer">
                    Simpan Konten Baru
                </button>
            </form>
        </section>
    </div>

    {{-- MODAL 1: LIHAT ISI KONTEN (READ) --}}
    <div id="modal-lihat-konten"
         role="dialog"
         aria-modal="true"
         aria-labelledby="lihat-judul"
         onclick="if(event.target === this) tutupModalKonten('modal-lihat-konten')"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl border border-stone-200 shadow-2xl max-w-2xl w-full max-h-[85vh] flex flex-col overflow-hidden">
            <div class="px-5 py-4 bg-[#133A26] text-white flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span id="lihat-jenis" class="px-2 py-0.5 rounded bg-[#F2C230] text-[#1F2421] font-bold text-[11px] uppercase"></span>
                        <span id="lihat-status" class="px-2 py-0.5 rounded bg-white/15 text-white font-semibold text-[11px] uppercase"></span>
                        <span id="lihat-jumlah-halaman" class="px-2 py-0.5 rounded bg-emerald-700 text-white font-semibold text-[11px] angka-tabular"></span>
                        <span id="lihat-tanggal" class="text-xs text-emerald-100 angka-tabular"></span>
                    </div>
                    <h3 id="lihat-judul" class="text-base sm:text-lg font-bold text-white"></h3>
                </div>
                <button type="button"
                        onclick="tutupModalKonten('modal-lihat-konten')"
                        aria-label="Tutup pratinjau"
                        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="p-5 overflow-y-auto space-y-4">
                <div id="lihat-ringkasan-box" class="p-3 rounded-xl bg-stone-100 border-l-4 border-[#1D6B45] text-xs text-stone-700 hidden">
                    <strong class="block text-stone-900 mb-0.5">Ringkasan Kartu:</strong>
                    <span id="lihat-ringkasan"></span>
                </div>

                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-stone-400 mb-2">Pratinjau Isi per Halaman Buku</div>
                    <div id="lihat-daftar-halaman" class="space-y-3"></div>
                </div>
            </div>

            <div class="px-5 py-3 bg-stone-50 border-t border-stone-200 flex items-center justify-end gap-2">
                <button type="button"
                        id="tombol-lihat-ke-edit"
                        class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold cursor-pointer">
                    Edit Halaman &amp; Teks
                </button>
                <button type="button"
                        onclick="tutupModalKonten('modal-lihat-konten')"
                        class="px-4 py-2 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-stone-700 text-xs font-semibold cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL 2: EDIT KONTEN (UPDATE) --}}
    <div id="modal-edit-konten"
         role="dialog"
         aria-modal="true"
         aria-labelledby="judul-modal-edit"
         onclick="if(event.target === this) tutupModalKonten('modal-edit-konten')"
         class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl border border-stone-200 shadow-2xl max-w-3xl w-full max-h-[90vh] flex flex-col overflow-hidden">
            <div class="px-5 py-4 bg-[#133A26] text-white flex items-center justify-between gap-4">
                <div>
                    <h3 id="judul-modal-edit" class="text-base font-bold">Edit Konten &amp; Halaman Buku Panduan</h3>
                    <p class="text-xs text-emerald-100">Tambah halaman baru, hapus halaman, urutkan, atau ubah isi teks pada tiap halaman.</p>
                </div>
                <button type="button"
                        onclick="tutupModalKonten('modal-edit-konten')"
                        aria-label="Tutup form edit"
                        class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 text-white flex items-center justify-center font-bold cursor-pointer">
                    ✕
                </button>
            </div>

            <form id="form-edit-konten" method="POST" action="#" class="p-5 overflow-y-auto space-y-4 text-sm">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit-judul" class="block text-xs font-semibold text-stone-700 mb-1">Judul</label>
                    <input id="edit-judul" name="judul" type="text" required maxlength="200"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="edit-jenis" class="block text-xs font-semibold text-stone-700 mb-1">Jenis Konten</label>
                        <select id="edit-jenis" name="jenis" required
                                class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                            <option value="edukasi">Edukasi</option>
                            <option value="pengumuman">Pengumuman</option>
                            <option value="artikel">Artikel</option>
                            <option value="banner">Banner</option>
                        </select>
                    </div>

                    <div>
                        <label for="edit-status" class="block text-xs font-semibold text-stone-700 mb-1">Status Publikasi</label>
                        <select id="edit-status" name="status" required
                                class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                            <option value="terbit">Terbit (Tampil di Beranda)</option>
                            <option value="draft">Draft</option>
                            <option value="arsip">Arsip</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="edit-ringkasan" class="block text-xs font-semibold text-stone-700 mb-1">Ringkasan Singkat / Intisari (Opsional)</label>
                    <input id="edit-ringkasan" name="ringkasan" type="text" maxlength="300"
                           placeholder="Ditampilkan pada kartu & kotak intisari halaman pertama"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm">
                </div>

                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                        <div>
                            <label class="block text-xs font-semibold text-stone-700">Susunan Halaman Buku Panduan</label>
                            <p class="text-[11px] text-stone-500">Setiap kotak di bawah ini mewakili 1 halaman di buku interaktif pada halaman depan.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="info-jumlah-edit" class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-bold angka-tabular">1 Halaman</span>
                            <button type="button"
                                    onclick="pecahParagrafKeHalaman('daftar-halaman-edit')"
                                    title="Pecah paragraf menjadi halaman-halaman terpisah"
                                    class="px-2.5 py-1 rounded-lg border border-stone-300 bg-stone-50 hover:bg-stone-100 text-stone-700 text-xs font-semibold cursor-pointer">
                                Bagi Otomatis per Paragraf
                            </button>
                            <button type="button"
                                    onclick="tambahHalaman('daftar-halaman-edit', '')"
                                    class="px-3 py-1 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs font-semibold cursor-pointer">
                                + Tambah Halaman
                            </button>
                        </div>
                    </div>

                    <div id="daftar-halaman-edit" data-label-counter="info-jumlah-edit" class="space-y-3 max-h-[340px] overflow-y-auto pr-1"></div>

                    <button type="button"
                            onclick="tambahHalaman('daftar-halaman-edit', '')"
                            class="mt-3 w-full py-2.5 px-3 rounded-xl border-2 border-dashed border-emerald-600/40 hover:border-emerald-600 bg-emerald-50/40 hover:bg-emerald-50 text-[#1D6B45] font-semibold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition-colors">
                        <span>+ Tambah Halaman Baru di Bagian Akhir</span>
                    </button>
                </div>

                <div class="pt-3 border-t border-stone-200 flex items-center justify-end gap-2">
                    <button type="button"
                            onclick="tutupModalKonten('modal-edit-konten')"
                            class="px-4 py-2 rounded-lg border border-stone-300 bg-white hover:bg-stone-100 text-stone-700 text-xs font-semibold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs font-semibold cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function ambilDataHalaman(id) {
            const scriptEl = document.getElementById('halaman-data-' + id);
            if (!scriptEl) return [''];
            try {
                const parsed = JSON.parse(scriptEl.textContent);
                return Array.isArray(parsed) && parsed.length ? parsed : [''];
            } catch (e) {
                return [''];
            }
        }

        function buatElemenHalaman(wadahId, teks) {
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-item-halaman', '');
            wrapper.className = 'rounded-xl border border-stone-200 bg-stone-50/70 p-3 space-y-2';

            const header = document.createElement('div');
            header.className = 'flex items-center justify-between gap-2';

            const label = document.createElement('span');
            label.setAttribute('data-nomor-halaman', '');
            label.className = 'text-xs font-bold text-[#133A26] uppercase tracking-wider';
            label.textContent = 'Halaman';

            const grupTombol = document.createElement('div');
            grupTombol.className = 'flex items-center gap-1';

            const btnNaik = document.createElement('button');
            btnNaik.type = 'button';
            btnNaik.title = 'Geser halaman ke atas';
            btnNaik.className = 'px-2 py-0.5 rounded border border-stone-300 bg-white hover:bg-stone-100 text-stone-600 text-xs font-bold cursor-pointer';
            btnNaik.textContent = '↑';
            btnNaik.onclick = function () { geserHalaman(btnNaik, wadahId, -1); };

            const btnTurun = document.createElement('button');
            btnTurun.type = 'button';
            btnTurun.title = 'Geser halaman ke bawah';
            btnTurun.className = 'px-2 py-0.5 rounded border border-stone-300 bg-white hover:bg-stone-100 text-stone-600 text-xs font-bold cursor-pointer';
            btnTurun.textContent = '↓';
            btnTurun.onclick = function () { geserHalaman(btnTurun, wadahId, 1); };

            const btnSisip = document.createElement('button');
            btnSisip.type = 'button';
            btnSisip.title = 'Sisipkan halaman baru di bawah halaman ini';
            btnSisip.className = 'px-2 py-0.5 rounded border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[11px] font-semibold cursor-pointer';
            btnSisip.textContent = '+ Sisip';
            btnSisip.onclick = function () { sisipkanHalamanSetelah(btnSisip, wadahId); };

            const btnHapus = document.createElement('button');
            btnHapus.type = 'button';
            btnHapus.setAttribute('data-btn-hapus', '');
            btnHapus.title = 'Kurangi / hapus halaman ini';
            btnHapus.className = 'px-2 py-0.5 rounded border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-[11px] font-semibold cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed';
            btnHapus.textContent = 'Hapus';
            btnHapus.onclick = function () { hapusHalaman(btnHapus, wadahId); };

            grupTombol.appendChild(btnNaik);
            grupTombol.appendChild(btnTurun);
            grupTombol.appendChild(btnSisip);
            grupTombol.appendChild(btnHapus);

            header.appendChild(label);
            header.appendChild(grupTombol);

            const textarea = document.createElement('textarea');
            textarea.name = 'halaman[]';
            textarea.rows = 4;
            textarea.required = true;
            textarea.placeholder = 'Tulis isi teks untuk halaman ini…';
            textarea.className = 'w-full px-3 py-2 rounded-lg border border-stone-300 bg-white text-sm leading-relaxed';
            textarea.value = teks || '';

            wrapper.appendChild(header);
            wrapper.appendChild(textarea);
            return wrapper;
        }

        function perbaruiNomorHalaman(wadahId) {
            const wadah = document.getElementById(wadahId);
            if (!wadah) return;
            const items = Array.from(wadah.querySelectorAll('[data-item-halaman]'));
            const total = items.length;

            items.forEach((item, idx) => {
                const label = item.querySelector('[data-nomor-halaman]');
                if (label) label.textContent = 'Halaman ' + (idx + 1) + ' dari ' + total;

                const btnHapus = item.querySelector('[data-btn-hapus]');
                if (btnHapus) btnHapus.disabled = total <= 1;
            });

            const idCounter = wadah.getAttribute('data-label-counter');
            if (idCounter) {
                const elCounter = document.getElementById(idCounter);
                if (elCounter) elCounter.textContent = total + ' Halaman';
            }
        }

        function tambahHalaman(wadahId, teks) {
            const wadah = document.getElementById(wadahId);
            if (!wadah) return;
            const elBaru = buatElemenHalaman(wadahId, teks || '');
            wadah.appendChild(elBaru);
            perbaruiNomorHalaman(wadahId);
            const ta = elBaru.querySelector('textarea');
            if (ta) {
                ta.focus();
                elBaru.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        function sisipkanHalamanSetelah(btn, wadahId) {
            const wadah = document.getElementById(wadahId);
            const item = btn.closest('[data-item-halaman]');
            if (!wadah || !item) return;
            const elBaru = buatElemenHalaman(wadahId, '');
            item.insertAdjacentElement('afterend', elBaru);
            perbaruiNomorHalaman(wadahId);
            const ta = elBaru.querySelector('textarea');
            if (ta) ta.focus();
        }

        function hapusHalaman(btn, wadahId) {
            const wadah = document.getElementById(wadahId);
            if (!wadah) return;
            const items = wadah.querySelectorAll('[data-item-halaman]');
            if (items.length <= 1) {
                alert('Buku panduan minimal harus memiliki 1 halaman.');
                return;
            }
            const item = btn.closest('[data-item-halaman]');
            if (item) {
                item.remove();
                perbaruiNomorHalaman(wadahId);
            }
        }

        function geserHalaman(btn, wadahId, arah) {
            const wadah = document.getElementById(wadahId);
            const item = btn.closest('[data-item-halaman]');
            if (!wadah || !item) return;

            if (arah < 0 && item.previousElementSibling) {
                wadah.insertBefore(item, item.previousElementSibling);
                perbaruiNomorHalaman(wadahId);
            } else if (arah > 0 && item.nextElementSibling) {
                wadah.insertBefore(item.nextElementSibling, item);
                perbaruiNomorHalaman(wadahId);
            }
        }

        function pecahParagrafKeHalaman(wadahId) {
            const wadah = document.getElementById(wadahId);
            if (!wadah) return;
            const semuaTeks = Array.from(wadah.querySelectorAll('textarea'))
                .map((ta) => ta.value.trim())
                .filter(Boolean)
                .join('\n\n');

            if (!semuaTeks) return;

            const potongan = semuaTeks.split(/\n\s*\n+/).map((s) => s.trim()).filter(Boolean);
            if (potongan.length <= 1) {
                alert('Pisahkan antar paragraf dengan baris kosong (Enter 2x) terlebih dahulu agar dapat dibagi otomatis ke beberapa halaman.');
                return;
            }

            wadah.innerHTML = '';
            potongan.forEach((teks) => {
                wadah.appendChild(buatElemenHalaman(wadahId, teks));
            });
            perbaruiNomorHalaman(wadahId);
        }

        function bukaModalLihatKonten(id) {
            const el = document.querySelector('[data-konten-id="' + id + '"]');
            const modal = document.getElementById('modal-lihat-konten');
            if (!el || !modal) return;

            const judul = el.getAttribute('data-judul') || '';
            const jenis = el.getAttribute('data-jenis') || '';
            const status = el.getAttribute('data-status') || '';
            const tanggal = el.getAttribute('data-tanggal') || '';
            const ringkasan = el.getAttribute('data-ringkasan') || '';
            const daftarHalaman = ambilDataHalaman(id);

            document.getElementById('lihat-judul').textContent = judul;
            document.getElementById('lihat-jenis').textContent = jenis;
            document.getElementById('lihat-status').textContent = status;
            document.getElementById('lihat-tanggal').textContent = tanggal;
            document.getElementById('lihat-jumlah-halaman').textContent = daftarHalaman.length + ' Halaman';

            const wadahLihat = document.getElementById('lihat-daftar-halaman');
            wadahLihat.innerHTML = '';
            daftarHalaman.forEach((isiHal, idx) => {
                const kartu = document.createElement('div');
                kartu.className = 'p-3.5 rounded-xl border border-stone-200 bg-[#FDFBF7] space-y-1.5';

                const kepala = document.createElement('div');
                kepala.className = 'flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-stone-500 border-b border-stone-200/80 pb-1';
                kepala.innerHTML = '<span>Halaman ' + (idx + 1) + '</span><span>Hal. ' + (idx + 1) + ' / ' + daftarHalaman.length + '</span>';

                const badan = document.createElement('div');
                badan.className = 'text-sm text-[#1F2421] leading-relaxed whitespace-pre-line';
                badan.textContent = isiHal;

                kartu.appendChild(kepala);
                kartu.appendChild(badan);
                wadahLihat.appendChild(kartu);
            });

            const boxRingkasan = document.getElementById('lihat-ringkasan-box');
            if (ringkasan) {
                document.getElementById('lihat-ringkasan').textContent = ringkasan;
                boxRingkasan.classList.remove('hidden');
            } else {
                boxRingkasan.classList.add('hidden');
            }

            const btnKeEdit = document.getElementById('tombol-lihat-ke-edit');
            if (btnKeEdit) {
                btnKeEdit.onclick = function () {
                    tutupModalKonten('modal-lihat-konten');
                    bukaModalEditKonten(id);
                };
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function bukaModalEditKonten(id) {
            const el = document.querySelector('[data-konten-id="' + id + '"]');
            const modal = document.getElementById('modal-edit-konten');
            const form = document.getElementById('form-edit-konten');
            const wadahEdit = document.getElementById('daftar-halaman-edit');
            if (!el || !modal || !form || !wadahEdit) return;

            form.action = el.getAttribute('data-action-update');
            document.getElementById('edit-judul').value = el.getAttribute('data-judul') || '';
            document.getElementById('edit-jenis').value = el.getAttribute('data-jenis') || 'edukasi';
            document.getElementById('edit-status').value = el.getAttribute('data-status') || 'terbit';
            document.getElementById('edit-ringkasan').value = el.getAttribute('data-ringkasan') || '';

            const daftarHalaman = ambilDataHalaman(id);
            wadahEdit.innerHTML = '';
            daftarHalaman.forEach((teksHal) => {
                wadahEdit.appendChild(buatElemenHalaman('daftar-halaman-edit', teksHal));
            });
            perbaruiNomorHalaman('daftar-halaman-edit');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function tutupModalKonten(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('DOMContentLoaded', () => {
            perbaruiNomorHalaman('daftar-halaman-buat');
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                tutupModalKonten('modal-lihat-konten');
                tutupModalKonten('modal-edit-konten');
            }
        });
    </script>

@endsection
