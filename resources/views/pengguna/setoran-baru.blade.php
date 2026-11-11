@extends('layouts.pengguna')

@section('title', 'Buat Manifes Setoran Baru')
@section('heading', 'Buat Manifes Setoran Limbah Medis & B3')
@section('subheading', 'Pilih fasilitas berizin, tentukan metode penyerahan, dan masukkan jumlah (per item) tiap klasifikasi limbah yang diserahkan.')

@section('content')

    <form method="POST" action="{{ route('pengguna.setoran.store') }}" id="form-manifes" class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start pb-24 lg:pb-0">
        @csrf

        {{-- KOLOM KIRI (8 Kolom): PILIH FASILITAS, METODE, & RINCIAN LIMBAH --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Langkah 1: Pilih Fasilitas Penampungan & Metode Penyerahan --}}
            <section class="bg-white border border-[#D6DDD7] rounded-2xl p-5 sm:p-6 space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-[#D6DDD7]">
                    <span class="w-6 h-6 rounded-full bg-[#133A26] text-[#F2C230] font-judul font-bold text-xs flex items-center justify-center">1</span>
                    <h2 class="font-judul font-bold text-base text-[#1F2421]">Tujuan Fasilitas Berizin &amp; Metode Penyerahan</h2>
                </div>

                {{-- Pilihan Fasilitas dengan Kartu Radio Informatif + Dropdown Rapi --}}
                <div class="space-y-3">
                    <label for="tps_id" class="block text-sm font-semibold text-[#1F2421]">
                        Pilih Fasilitas Penampungan / TPS Tujuan <span class="text-[#B3261E]">*</span>
                    </label>

                    @php
                        $tpsTerpilihAwal = old('tps_id', $daftarTps->first()?->tps_id);
                    @endphp

                    {{-- Kartu Radio Fasilitas (Menampilkan Nama, Badge Izin, Jam Buka, Status, & Sisa Kuota) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Kartu pilihan fasilitas TPS">
                        @foreach ($daftarTps as $tps)
                            @php
                                $sisaKuota = max(0, (float) $tps->kapasitas_total_kg - (float) $tps->kapasitas_terpakai_kg);
                                $jamBuka = substr((string) ($tps->jam_buka ?? '07:00'), 0, 5);
                                $jamTutup = substr((string) ($tps->jam_tutup ?? '17:00'), 0, 5);
                                $aktifKartu = (int) $tpsTerpilihAwal === (int) $tps->tps_id;
                            @endphp
                            <div onclick="pilihKartuTps({{ $tps->tps_id }})"
                                 id="kartu-tps-{{ $tps->tps_id }}"
                                 class="p-3.5 rounded-xl border-2 cursor-pointer transition space-y-2 {{ $aktifKartu ? 'border-[#1D6B45] bg-[#E3EFE7]/40' : 'border-[#D6DDD7] bg-white hover:border-[#8A968E]' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="font-bold text-sm text-[#1F2421]">{{ $tps->nama_tps }}</div>
                                    @if ($tps->izin_limbah_medis)
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#133A26] text-[#F2C230] shrink-0">
                                            Berizin medis
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#F3F5F2] text-[#4A554E] border border-[#D6DDD7] shrink-0">
                                            Non-medis
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-[#4A554E] line-clamp-1">{{ $tps->alamat }}</p>
                                <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-[#D6DDD7]/70 text-xs angka-tabular">
                                    <span class="text-[#4A554E]">Jam: <strong class="text-[#1F2421]">{{ $jamBuka }}–{{ $jamTutup }}</strong></span>
                                    <span class="text-[#1D6B45] font-bold">Sisa kuota: {{ number_format($sisaKuota, 0, ',', '.') }} item</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Dropdown sinkron dengan teks ringkas yang mudah dipindai --}}
                    <select id="tps_id" name="tps_id" required onchange="perbaruiKalkulasiManifes()"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-[#8A968E] bg-white text-sm font-semibold text-[#1F2421]">
                        @foreach ($daftarTps as $tps)
                            @php
                                $sisaKuota = max(0, (float) $tps->kapasitas_total_kg - (float) $tps->kapasitas_terpakai_kg);
                                $jamBuka = substr((string) ($tps->jam_buka ?? '07:00'), 0, 5);
                                $jamTutup = substr((string) ($tps->jam_tutup ?? '17:00'), 0, 5);
                            @endphp
                            <option value="{{ $tps->tps_id }}"
                                    data-izin-medis="{{ $tps->izin_limbah_medis ? '1' : '0' }}"
                                    data-nama="{{ $tps->nama_tps }}"
                                    data-sisa-kuota="{{ $sisaKuota }}"
                                    data-jam="{{ $jamBuka }}–{{ $jamTutup }} WIB"
                                    @selected((int) $tpsTerpilihAwal === (int) $tps->tps_id)>
                                {{ $tps->nama_tps }} · {{ $tps->izin_limbah_medis ? 'Berizin medis' : 'B3 & daur ulang' }} · sisa {{ number_format($sisaKuota, 0, ',', '.') }} item ({{ $jamBuka }}–{{ $jamTutup }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-[#4A554E]">
                        Penyetoran <strong>Limbah Medis</strong> wajib memilih fasilitas dengan lencana <strong>Berizin medis</strong>.
                    </p>
                </div>

                {{-- Pilih Metode --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @php $metodeLama = old('metode', 'dijemput'); @endphp
                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 {{ $metodeLama === 'dijemput' ? 'border-[#1D6B45] bg-[#E3EFE7]/40' : 'border-[#D6DDD7] bg-white' }}" id="label-metode-dijemput">
                        <input type="radio" name="metode" value="dijemput" @checked($metodeLama === 'dijemput') onchange="ubahMetodeManifes()" class="mt-1 accent-[#1D6B45]">
                        <div class="text-xs">
                            <div class="font-bold text-sm text-[#1F2421]">Dijemput Kurir Khusus</div>
                            <p class="text-[#4A554E] mt-0.5">Kurir mengambil kemasan tersegel dari titik koordinat alamat Anda.</p>
                        </div>
                    </label>

                    <label class="p-3.5 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 {{ $metodeLama === 'antar_sendiri' ? 'border-[#1D6B45] bg-[#E3EFE7]/40' : 'border-[#D6DDD7] bg-white' }}" id="label-metode-antar">
                        <input type="radio" name="metode" value="antar_sendiri" @checked($metodeLama === 'antar_sendiri') onchange="ubahMetodeManifes()" class="mt-1 accent-[#1D6B45]">
                        <div class="text-xs">
                            <div class="font-bold text-sm text-[#1F2421]">Antar Sendiri ke Fasilitas</div>
                            <p class="text-[#4A554E] mt-0.5">Bawa langsung kemasan ke pos penerimaan fasilitas pada jam operasional.</p>
                        </div>
                    </label>
                </div>

                {{-- Pilih Alamat (Hanya tampil jika metode = dijemput) --}}
                <div id="wadah-alamat-jemput" class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="alamat_id" class="block text-xs font-semibold text-[#1F2421]">
                            Alamat Titik Penjemputan <span class="text-[#B3261E]">*</span>
                        </label>
                        <a href="{{ route('pengguna.alamat') }}" class="text-xs font-semibold text-[#1D6B45] hover:underline">
                            + Tambah Alamat Baru
                        </a>
                    </div>

                    @if ($daftarAlamat->isEmpty())
                        <div class="p-3.5 rounded-xl bg-[#FFF8E1] border border-[#F2C230] text-xs text-[#1F2421] flex items-center justify-between gap-3">
                            <span>Anda belum memiliki alamat penjemputan terdaftar. Tambahkan alamat terlebih dahulu atau pilih metode Antar Sendiri.</span>
                            <a href="{{ route('pengguna.alamat') }}" class="px-3 py-1.5 rounded-lg bg-[#1D6B45] text-white font-semibold shrink-0">
                                Tambah Alamat
                            </a>
                        </div>
                    @else
                        <select id="alamat_id" name="alamat_id" class="w-full px-3.5 py-2.5 rounded-xl border border-[#8A968E] bg-white text-sm text-[#1F2421]">
                            @foreach ($daftarAlamat as $al)
                                <option value="{{ $al->alamat_id }}" @selected(old('alamat_id') == $al->alamat_id || $al->is_default)>
                                    {{ $al->label }} {{ $al->is_default ? '(Utama)' : '' }} — {{ $al->alamat_lengkap }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Jadwal & Catatan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jadwal_waktu" class="block text-xs font-semibold text-[#1F2421] mb-1">
                            Rencana Waktu Jemput / Tiba (Opsional)
                        </label>
                        <input id="jadwal_waktu" name="jadwal_waktu" type="datetime-local"
                               min="{{ now()->addMinutes(30)->format('Y-m-d\TH:i') }}"
                               value="{{ old('jadwal_waktu', '') }}"
                               class="w-full px-3 py-2.5 rounded-xl border border-[#8A968E] bg-white text-sm angka-tabular text-[#1F2421]">
                        <p class="text-xs text-[#4A554E] mt-1">
                            Kosongkan bila fleksibel (otomatis dijadwalkan +2 jam ke depan). Contoh: {{ now()->addDay()->format('d/m/Y') }} 09:00.
                        </p>
                    </div>

                    <div>
                        <label for="catatan_pengguna" class="block text-xs font-semibold text-[#1F2421] mb-1">
                            Catatan Kemasan / Patokan Lokasi (Opsional)
                        </label>
                        <input id="catatan_pengguna" name="catatan_pengguna" type="text" maxlength="255"
                               value="{{ old('catatan_pengguna') }}"
                               placeholder="Mis: 1 Safety Box tersegel + 2 kantong kuning"
                               class="w-full px-3 py-2.5 rounded-xl border border-[#8A968E] bg-white text-sm text-[#1F2421]">
                        <p class="text-xs text-[#4A554E] mt-1">
                            Bantu kurir atau petugas pos mengenali jumlah wadah/item Anda.
                        </p>
                    </div>
                </div>
            </section>

            {{-- Langkah 2: Pilih Klasifikasi Limbah & Jumlah per Item --}}
            <section class="bg-white border border-[#D6DDD7] rounded-2xl p-5 sm:p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-[#D6DDD7]">
                    <div class="flex items-center gap-2.5">
                        <span class="w-6 h-6 rounded-full bg-[#133A26] text-[#F2C230] font-judul font-bold text-xs flex items-center justify-center">2</span>
                        <div>
                            <h2 class="font-judul font-bold text-base text-[#1F2421]">Rincian Item Limbah yang Diserahkan</h2>
                            <p class="text-xs text-[#4A554E]">Isi jumlah satuan (minimal 1 item) pada jenis limbah medis atau B3 yang ingin Anda serahkan.</p>
                        </div>
                    </div>
                    {{-- Pil informasi hijau/netral, bukan merah galat --}}
                    <span id="info-opsi-medis" class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#E3EFE7] text-[#133A26]">
                        Fasilitas Berizin Medis · Opsi Medis Tersedia
                    </span>
                </div>

                <div class="divide-y divide-[#D6DDD7]">
                    @foreach ($jenisSampah as $j)
                        @php
                            $nilaiLama = (int) old("berat_estimasi.{$j->jenis_id}", 0);
                            $badgeKat = match ($j->kategori) {
                                'medis'      => ['bg' => 'bg-[#FFF8E1] text-[#7A5200] border-[#F2C230]', 'label' => 'Medis Khusus'],
                                'b3'         => ['bg' => 'bg-[#FFF8E1] text-[#7A5200] border-[#F2C230]', 'label' => 'Limbah B3'],
                                'elektronik' => ['bg' => 'bg-[#E3EFE7] text-[#133A26] border-[#1D6B45]/30', 'label' => 'E-Waste'],
                                'anorganik'  => ['bg' => 'bg-[#E3EFE7] text-[#133A26] border-[#1D6B45]/30', 'label' => 'Anorganik'],
                                default      => ['bg' => 'bg-[#F3F5F2] text-[#4A554E] border-[#D6DDD7]', 'label' => 'Organik'],
                            };
                        @endphp
                        {{-- Pola tetap 3 bagian: Baris 1 (Nama + 1 Badge), Baris 2 (Deskripsi), Baris 3 (Keterangan Poin per item) --}}
                        <div data-baris-kategori="{{ $j->kategori }}"
                             class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="min-w-0 flex-1 space-y-1">
                                {{-- Baris 1: Nama jenis + satu badge --}}
                                <div class="flex items-center gap-2 flex-wrap">
                                    <label for="berat-jenis-{{ $j->jenis_id }}" class="font-bold text-sm text-[#1F2421] cursor-pointer">
                                        {{ $j->nama }}
                                    </label>
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded border {{ $badgeKat['bg'] }}">
                                        {{ $badgeKat['label'] }}
                                    </span>
                                </div>

                                {{-- Baris 2: Deskripsi --}}
                                <p class="text-[13px] text-[#4A554E] leading-snug">{{ $j->deskripsi }}</p>

                                {{-- Baris 3: Informasi poin per item --}}
                                <div class="text-xs angka-tabular pt-0.5">
                                    @if ($j->poin_per_kg > 0)
                                        <span class="font-semibold text-[#1D6B45]">+{{ $j->poin_per_kg }} poin/item</span>
                                    @else
                                        <span class="font-medium text-[#4A554E]">Pemusnahan berizin · 0 poin</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Stepper per item (bilangan bulat, target sentuh 44x44px w-11 h-11) --}}
                            <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                <button type="button"
                                        onclick="ubahBeratForm({{ $j->jenis_id }}, -1)"
                                        aria-label="Kurangi 1 item {{ $j->nama }}"
                                        class="w-11 h-11 rounded-xl border border-[#8A968E] bg-[#F3F5F2] hover:bg-[#E2E8E3] text-[#1F2421] font-bold text-base flex items-center justify-center cursor-pointer transition">
                                    −
                                </button>
                                <input type="number"
                                       id="berat-jenis-{{ $j->jenis_id }}"
                                       name="berat_estimasi[{{ $j->jenis_id }}]"
                                       inputmode="numeric"
                                       step="1"
                                       min="0"
                                       max="1000"
                                       value="{{ $nilaiLama }}"
                                       data-nama="{{ $j->nama }}"
                                       data-kategori="{{ $j->kategori }}"
                                       data-poin="{{ $j->poin_per_kg }}"
                                       oninput="perbaruiKalkulasiManifes()"
                                       class="w-20 h-11 px-2.5 rounded-xl border border-[#8A968E] bg-white text-sm font-bold text-right angka-tabular text-[#1F2421]">
                                <span class="text-xs font-semibold text-[#4A554E] w-8">item</span>
                                <button type="button"
                                        onclick="ubahBeratForm({{ $j->jenis_id }}, 1)"
                                        aria-label="Tambah 1 item {{ $j->nama }}"
                                        class="w-11 h-11 rounded-xl bg-[#1D6B45] hover:bg-[#133A26] text-white font-bold text-base flex items-center justify-center cursor-pointer transition">
                                    +
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- KOLOM KANAN (4 Kolom): RINGKASAN NOTA MANIFES, VALIDASI KUOTA & PERNYATAAN SOP --}}
        <div id="panel-ringkasan-manifes" class="lg:col-span-4 lg:sticky lg:top-6 space-y-4">
            <div class="bg-[#133A26] text-white rounded-2xl p-5 shadow-md space-y-4">
                <div class="flex items-center justify-between gap-2 border-b border-white/15 pb-3">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#F2C230] block">Pratinjau Dokumen</span>
                        <h3 class="font-judul text-base font-bold">Ringkasan Manifes Setoran</h3>
                    </div>
                    <span id="badge-status-medis" class="px-2.5 py-1 rounded-md bg-white/15 text-white font-judul font-bold text-xs shrink-0">
                        BELUM LENGKAP
                    </span>
                </div>

                {{-- Daftar Rincian Item Terpilih --}}
                <div class="bg-white text-[#1F2421] rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between gap-2 text-xs text-[#4A554E] border-b border-dashed border-[#D6DDD7] pb-2.5 angka-tabular">
                        <div class="truncate">Tujuan: <strong id="ringkasan-nama-tps" class="text-[#1F2421]">-</strong></div>
                        <div class="shrink-0">Kuota: <strong id="ringkasan-kuota-tps" class="text-[#1D6B45]">-</strong></div>
                    </div>

                    <dl id="ringkasan-item-list" class="space-y-1.5 text-xs angka-tabular">
                        <div class="text-[#4A554E] py-1">Belum ada jenis limbah yang diisi jumlah itemnya.</div>
                    </dl>

                    {{-- Peringatan jika total item melebihi sisa kuota TPS --}}
                    <div id="peringatan-kuota" role="alert" class="hidden p-2.5 rounded-lg bg-[#FCE8E6] border border-[#F2B8B5] text-xs font-semibold text-[#B3261E]">
                        Total item melebihi sisa daya tampung fasilitas ini. Kurangi jumlah item atau pilih fasilitas lain.
                    </div>

                    <div class="pt-2.5 border-t border-dashed border-[#D6DDD7] space-y-1.5 text-xs angka-tabular">
                        <div id="baris-ringkasan-medis" class="flex justify-between text-[#4A554E] font-semibold">
                            <span>Sub-total Limbah Medis:</span>
                            <span id="ringkasan-berat-medis" class="text-[#1F2421]">0 item</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold text-[#1F2421]">
                            <span>Total Item Diserahkan:</span>
                            <span id="ringkasan-total-berat">0 item</span>
                        </div>
                        <div class="flex justify-between text-sm font-extrabold text-[#1D6B45]">
                            <span>Estimasi Poin Insentif B3:</span>
                            <span id="ringkasan-total-poin">+0 poin</span>
                        </div>
                    </div>
                </div>

                {{-- Pernyataan Wajib Kemasan Sesuai SOP K3 --}}
                <label for="pernyataan_sop" class="flex items-start gap-2.5 p-3 rounded-xl bg-white/10 border border-white/20 cursor-pointer text-xs leading-relaxed">
                    <input type="checkbox" id="pernyataan_sop" name="pernyataan_sop" value="1"
                           @checked(old('pernyataan_sop'))
                           onchange="perbaruiKalkulasiManifes()"
                           required
                           class="mt-0.5 w-4 h-4 rounded accent-[#F2C230] shrink-0">
                    <span class="text-white/95">
                        Saya memastikan <strong>benda tajam</strong> berada di dalam wadah tahan tusuk (<em>Safety Box</em>) dan <strong>kantong limbah infeksius</strong> telah terikat rapat sesuai SOP K3.
                    </span>
                </label>

                <div class="space-y-2">
                    <button type="submit" id="tombol-submit-manifes" disabled
                            class="w-full py-3 px-4 rounded-xl bg-[#F2C230] hover:bg-[#e0b020] disabled:bg-white/20 disabled:text-white/60 disabled:cursor-not-allowed text-[#1F2421] font-judul font-extrabold text-sm text-center cursor-pointer transition">
                        Terbitkan Tiket Manifes Setoran
                    </button>
                    <p id="teks-bantu-submit" class="text-xs text-center text-emerald-100/90">
                        Isi minimal 1 item limbah untuk menerbitkan tiket.
                    </p>
                </div>
            </div>
        </div>

        {{-- BAR BAWAH MENEMPEL DI PONSEL (Agar ringkasan & tombol tetap terjangkau di layar kecil) --}}
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-[#133A26] text-white border-t border-white/15 px-4 py-3 shadow-lg flex items-center justify-between gap-3">
            <div class="min-w-0 angka-tabular">
                <div class="text-xs text-emerald-100/90 truncate" id="mobile-status-teks">Belum ada item limbah diisi</div>
                <div class="text-sm font-bold text-white">
                    Total: <span id="mobile-total-berat">0 item</span> · <span id="mobile-total-poin" class="text-[#F2C230]">+0 pts</span>
                </div>
            </div>
            <a href="#panel-ringkasan-manifes"
               class="px-3.5 py-2 rounded-xl bg-[#F2C230] text-[#1F2421] font-judul font-bold text-xs shrink-0">
                Periksa &amp; Kirim ↓
            </a>
        </div>
    </form>

    <script>
        function pilihKartuTps(tpsId) {
            const selectTps = document.getElementById('tps_id');
            if (!selectTps) return;
            selectTps.value = String(tpsId);
            perbaruiKalkulasiManifes();
        }

        function ubahMetodeManifes() {
            const metode = document.querySelector('input[name="metode"]:checked')?.value || 'dijemput';
            const wadahAlamat = document.getElementById('wadah-alamat-jemput');
            const lblJemput = document.getElementById('label-metode-dijemput');
            const lblAntar = document.getElementById('label-metode-antar');

            if (wadahAlamat) {
                wadahAlamat.style.display = metode === 'dijemput' ? 'block' : 'none';
            }
            if (lblJemput && lblAntar) {
                lblJemput.className = 'p-3.5 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 ' + (metode === 'dijemput' ? 'border-[#1D6B45] bg-[#E3EFE7]/40' : 'border-[#D6DDD7] bg-white');
                lblAntar.className = 'p-3.5 rounded-xl border-2 cursor-pointer transition flex items-start gap-3 ' + (metode === 'antar_sendiri' ? 'border-[#1D6B45] bg-[#E3EFE7]/40' : 'border-[#D6DDD7] bg-white');
            }
        }

        function ubahBeratForm(jenisId, delta) {
            const input = document.getElementById('berat-jenis-' + jenisId);
            if (!input || input.disabled) return;
            const skrg = parseInt(input.value, 10) || 0;
            const baru = Math.max(0, skrg + delta);
            input.value = baru;
            perbaruiKalkulasiManifes();
        }

        function perbaruiKalkulasiManifes() {
            const selectTps = document.getElementById('tps_id');
            const optTps = selectTps ? selectTps.options[selectTps.selectedIndex] : null;
            const tpsIdTerpilih = selectTps ? selectTps.value : '';
            const izinMedisTps = optTps ? optTps.getAttribute('data-izin-medis') === '1' : false;
            const namaTps = optTps ? optTps.getAttribute('data-nama') : '-';
            const sisaKuota = optTps ? (parseFloat(optTps.getAttribute('data-sisa-kuota')) || 0) : 0;

            // Sorot kartu fasilitas yang aktif
            document.querySelectorAll('[id^="kartu-tps-"]').forEach((kartu) => {
                const aktif = kartu.id === 'kartu-tps-' + tpsIdTerpilih;
                kartu.className = 'p-3.5 rounded-xl border-2 cursor-pointer transition space-y-2 ' + (aktif ? 'border-[#1D6B45] bg-[#E3EFE7]/40' : 'border-[#D6DDD7] bg-white hover:border-[#8A968E]');
            });

            const elNamaTps = document.getElementById('ringkasan-nama-tps');
            if (elNamaTps) elNamaTps.textContent = namaTps;

            const elKuotaTps = document.getElementById('ringkasan-kuota-tps');
            if (elKuotaTps) {
                elKuotaTps.textContent = sisaKuota.toLocaleString('id-ID', {maximumFractionDigits: 0}) + ' item';
            }

            // Sembunyikan & nonaktifkan seluruh opsi Limbah Medis jika TPS tidak memiliki izin limbah medis
            const barisMedis = document.querySelectorAll('[data-baris-kategori="medis"]');
            barisMedis.forEach((baris) => {
                const inputMedis = baris.querySelector('input[type="number"]');
                if (!izinMedisTps) {
                    baris.classList.add('hidden');
                    if (inputMedis) {
                        inputMedis.value = 0;
                        inputMedis.disabled = true;
                    }
                } else {
                    baris.classList.remove('hidden');
                    if (inputMedis) {
                        inputMedis.disabled = false;
                    }
                }
            });

            // Perbarui pil informasi langkah 2 (hijau klinis/netral)
            const infoOpsiMedis = document.getElementById('info-opsi-medis');
            const barisRingkasanMedis = document.getElementById('baris-ringkasan-medis');
            if (infoOpsiMedis) {
                if (izinMedisTps) {
                    infoOpsiMedis.textContent = 'Fasilitas Berizin Medis · Opsi Medis Tersedia';
                    infoOpsiMedis.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#E3EFE7] text-[#133A26]';
                } else {
                    infoOpsiMedis.textContent = 'Tanpa Izin Medis · Hanya Limbah B3 & Non-Medis';
                    infoOpsiMedis.className = 'px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#F3F5F2] text-[#4A554E]';
                }
            }
            if (barisRingkasanMedis) {
                barisRingkasanMedis.style.display = izinMedisTps ? 'flex' : 'none';
            }

            const inputs = document.querySelectorAll('[id^="berat-jenis-"]');
            let totalItem = 0;
            let itemMedis = 0;
            let totalPoinMentah = 0;
            let htmlList = '';

            inputs.forEach((inp) => {
                if (inp.disabled) return;
                const jmlItem = Math.max(0, Math.round(parseFloat(inp.value) || 0));
                if (jmlItem > 0) {
                    const nama = inp.getAttribute('data-nama');
                    const kat = inp.getAttribute('data-kategori');
                    const poin = parseFloat(inp.getAttribute('data-poin')) || 0;

                    totalItem += jmlItem;
                    if (kat === 'medis') itemMedis += jmlItem;
                    totalPoinMentah += (jmlItem * poin);

                    htmlList += `
                        <div class="flex items-center justify-between text-[#1F2421]">
                            <dt class="truncate pr-2 font-medium">${nama}</dt>
                            <dd class="font-bold shrink-0">${jmlItem.toLocaleString('id-ID')} item</dd>
                        </div>
                    `;
                }
            });

            const wadahList = document.getElementById('ringkasan-item-list');
            if (wadahList) {
                wadahList.innerHTML = htmlList || '<div class="text-[#4A554E] py-1">Belum ada jenis limbah yang diisi jumlah itemnya.</div>';
            }

            const teksItemMedis = itemMedis.toLocaleString('id-ID') + ' item';
            const teksTotalItem = totalItem.toLocaleString('id-ID') + ' item';
            const poinBulat = Math.floor(totalPoinMentah);

            document.getElementById('ringkasan-berat-medis').textContent = teksItemMedis;
            document.getElementById('ringkasan-total-berat').textContent = teksTotalItem;
            document.getElementById('ringkasan-total-poin').textContent = '+' + poinBulat.toLocaleString('id-ID') + ' poin';

            // Sinkronkan bar bawah mobile
            const mobBerat = document.getElementById('mobile-total-berat');
            const mobPoin = document.getElementById('mobile-total-poin');
            const mobStatus = document.getElementById('mobile-status-teks');
            if (mobBerat) mobBerat.textContent = teksTotalItem;
            if (mobPoin) mobPoin.textContent = '+' + poinBulat.toLocaleString('id-ID') + ' pts';

            // Validasi status form: kosong vs melebihi kuota vs belum centang SOP vs siap kirim
            const badgeStatus = document.getElementById('badge-status-medis');
            const tombolSubmit = document.getElementById('tombol-submit-manifes');
            const teksBantu = document.getElementById('teks-bantu-submit');
            const peringatanKuota = document.getElementById('peringatan-kuota');
            const sopDicentang = document.getElementById('pernyataan_sop')?.checked || false;
            const lewatKuota = totalItem > sisaKuota;

            if (peringatanKuota) {
                peringatanKuota.classList.toggle('hidden', !lewatKuota);
            }

            if (totalItem <= 0) {
                if (badgeStatus) {
                    badgeStatus.textContent = 'BELUM LENGKAP';
                    badgeStatus.className = 'px-2.5 py-1 rounded-md bg-white/15 text-white font-judul font-bold text-xs shrink-0';
                }
                if (tombolSubmit) tombolSubmit.disabled = true;
                if (teksBantu) teksBantu.textContent = 'Isi minimal 1 item limbah untuk menerbitkan tiket.';
                if (mobStatus) mobStatus.textContent = 'Belum lengkap · isi minimal 1 item limbah';
            } else if (lewatKuota) {
                if (badgeStatus) {
                    badgeStatus.textContent = 'MELEBIHI KUOTA';
                    badgeStatus.className = 'px-2.5 py-1 rounded-md bg-[#FCE8E6] text-[#B3261E] font-judul font-bold text-xs shrink-0';
                }
                if (tombolSubmit) tombolSubmit.disabled = true;
                if (teksBantu) teksBantu.textContent = 'Kurangi jumlah item agar tidak melewati sisa kuota fasilitas.';
                if (mobStatus) mobStatus.textContent = 'Melebihi sisa kuota fasilitas!';
            } else if (!sopDicentang) {
                if (badgeStatus) {
                    badgeStatus.textContent = 'KONFIRMASI SOP';
                    badgeStatus.className = 'px-2.5 py-1 rounded-md bg-[#FFF8E1] text-[#7A5200] font-judul font-bold text-xs shrink-0';
                }
                if (tombolSubmit) tombolSubmit.disabled = true;
                if (teksBantu) teksBantu.textContent = 'Centang pernyataan kemasan SOP K3 di atas untuk mengaktifkan tombol.';
                if (mobStatus) mobStatus.textContent = 'Centang pernyataan SOP K3 untuk kirim';
            } else {
                if (badgeStatus) {
                    badgeStatus.textContent = 'SIAP KIRIM';
                    badgeStatus.className = 'px-2.5 py-1 rounded-md bg-[#F2C230] text-[#1F2421] font-judul font-extrabold text-xs shrink-0';
                }
                if (tombolSubmit) tombolSubmit.disabled = false;
                if (teksBantu) teksBantu.textContent = 'Data lengkap & sesuai kuota. Siap diterbitkan.';
                if (mobStatus) mobStatus.textContent = 'Siap kirim ke ' + namaTps;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            ubahMetodeManifes();
            perbaruiKalkulasiManifes();
        });
    </script>

@endsection
