@extends('layouts.pengelola')

@section('title', 'Izin & Profil Fasilitas Medis/B3')
@section('heading', $tps ? 'Izin & Operasional · '.$tps->nama_tps : 'Daftarkan Fasilitas Penampungan Anda')
@section('subheading', $tps ? 'Atur status penerimaan manifes (buka/tutup/penuh), nomor izin operasional DLHKP, dan koordinat fasilitas.' : '1 akun pengelola terhubung secara eksklusif ke 1 unit fasilitas penampungan.')

@section('content')

    @if ($tps)
        {{-- Pengaturan 1 TPS Milik Akun Pengelola Ini --}}
        <section class="bg-white border border-stone-200 rounded-xl p-6 max-w-2xl">
            <div class="flex items-center justify-between gap-2 mb-5 pb-4 border-b border-stone-200">
                <div>
                    <h2 class="text-base font-bold text-[#1F2421]">Pengaturan {{ $tps->nama_tps }}</h2>
                    <p class="text-xs text-stone-500">Perbarui status gerbang dan jam operasional pos TPS Anda.</p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($tps->izin_limbah_medis)
                        <span class="px-2.5 py-0.5 text-xs font-bold rounded bg-red-100 text-red-900">
                            Berizin Limbah Medis
                        </span>
                    @endif
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded {{ $tps->is_verified ? 'bg-emerald-100 text-emerald-900' : 'bg-amber-100 text-amber-900' }}">
                        {{ $tps->is_verified ? 'Terverifikasi Admin' : 'Menunggu Verifikasi Admin' }}
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('pengelola.profil-tps.update') }}" class="space-y-4 text-sm">
                @csrf
                @method('PATCH')

                <div>
                    <label for="status" class="block text-xs font-semibold text-stone-700 mb-1">Status Gerbang TPS Saat Ini</label>
                    <select id="status" name="status" class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white font-semibold">
                        <option value="buka" @selected($tps->status === 'buka')>BUKA (Menerima Setoran)</option>
                        <option value="tutup" @selected($tps->status === 'tutup')>TUTUP SEMENTARA</option>
                        <option value="penuh" @selected($tps->status === 'penuh')>PENUH</option>
                    </select>
                </div>

                <div>
                    <label for="nama_tps" class="block text-xs font-semibold text-stone-700 mb-1">Nama TPS</label>
                    <input id="nama_tps" name="nama_tps" type="text" value="{{ old('nama_tps', $tps->nama_tps) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                </div>

                <div>
                    <label for="alamat" class="block text-xs font-semibold text-stone-700 mb-1">Alamat Lengkap</label>
                    <input id="alamat" name="alamat" type="text" value="{{ old('alamat', $tps->alamat) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="latitude" class="block text-xs font-semibold text-stone-700 mb-1">Latitude</label>
                        <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $tps->latitude) }}" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                    <div>
                        <label for="longitude" class="block text-xs font-semibold text-stone-700 mb-1">Longitude</label>
                        <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $tps->longitude) }}" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jam_buka" class="block text-xs font-semibold text-stone-700 mb-1">Jam Buka</label>
                        <input id="jam_buka" name="jam_buka" type="time" value="{{ old('jam_buka', $tps->jam_buka ? substr($tps->jam_buka, 0, 5) : '07:00') }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                    <div>
                        <label for="jam_tutup" class="block text-xs font-semibold text-stone-700 mb-1">Jam Tutup</label>
                        <input id="jam_tutup" name="jam_tutup" type="time" value="{{ old('jam_tutup', $tps->jam_tutup ? substr($tps->jam_tutup, 0, 5) : '17:00') }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="kapasitas_total_kg" class="block text-xs font-semibold text-stone-700 mb-1">Kapasitas Total (kg)</label>
                        <input id="kapasitas_total_kg" name="kapasitas_total_kg" type="number" min="10" step="10"
                               value="{{ old('kapasitas_total_kg', $tps->kapasitas_total_kg) }}" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                    <div>
                        <label for="no_izin" class="block text-xs font-semibold text-stone-700 mb-1">Nomor Izin Operasional</label>
                        <input id="no_izin" name="no_izin" type="text" value="{{ old('no_izin', $tps->no_izin) }}"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white font-semibold cursor-pointer">
                        Simpan Pengaturan TPS
                    </button>
                </div>
            </form>
        </section>
    @else
        {{-- Hanya tampil jika akun pengelola ini belum memiliki TPS sama sekali --}}
        <section class="bg-white border border-stone-200 rounded-xl p-6 max-w-2xl">
            <h2 class="text-base font-bold text-[#1F2421]">Daftarkan Lokasi TPS Anda</h2>
            <p class="text-xs text-stone-500 mt-0.5 mb-5">
                Setiap akun pengelola mengelola 1 lokasi TPS. Setelah didaftarkan, Administrator akan memverifikasi izin TPS Anda.
            </p>

            <form method="POST" action="{{ route('pengelola.profil-tps.store') }}" class="space-y-4 text-sm">
                @csrf

                <div>
                    <label for="baru_nama_tps" class="block text-xs font-semibold text-stone-700 mb-1">Nama TPS</label>
                    <input id="baru_nama_tps" name="nama_tps" type="text" required placeholder="Mis. TPS Melati Pesantren"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                </div>

                <div>
                    <label for="baru_alamat" class="block text-xs font-semibold text-stone-700 mb-1">Alamat Lengkap</label>
                    <input id="baru_alamat" name="alamat" type="text" required placeholder="Jl. Brigjen Pol. Imam Bachri No. 10, Kota Kediri"
                           class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="baru_latitude" class="block text-xs font-semibold text-stone-700 mb-1">Latitude</label>
                        <input id="baru_latitude" name="latitude" type="number" step="0.0000001" value="-7.8225" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                    <div>
                        <label for="baru_longitude" class="block text-xs font-semibold text-stone-700 mb-1">Longitude</label>
                        <input id="baru_longitude" name="longitude" type="number" step="0.0000001" value="112.0318" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="baru_kapasitas" class="block text-xs font-semibold text-stone-700 mb-1">Kapasitas Total (kg)</label>
                        <input id="baru_kapasitas" name="kapasitas_total_kg" type="number" min="10" step="10" value="2000" required
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                    <div>
                        <label for="baru_izin" class="block text-xs font-semibold text-stone-700 mb-1">Nomor Izin (Opsional)</label>
                        <input id="baru_izin" name="no_izin" type="text" placeholder="IZN-DLHKP/2026/..."
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="baru_buka" class="block text-xs font-semibold text-stone-700 mb-1">Jam Buka</label>
                        <input id="baru_buka" name="jam_buka" type="time" value="07:00"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                    <div>
                        <label for="baru_tutup" class="block text-xs font-semibold text-stone-700 mb-1">Jam Tutup</label>
                        <input id="baru_tutup" name="jam_tutup" type="time" value="17:00"
                               class="w-full px-3 py-2 rounded-lg border border-stone-300 bg-white angka-tabular">
                    </div>
                </div>

                <label class="flex items-center gap-2 pt-1 text-xs font-medium text-stone-700 cursor-pointer">
                    <input type="checkbox" name="izin_limbah_medis" value="1"
                           class="w-4 h-4 rounded accent-[#1D6B45]">
                    <span>Ajukan verifikasi izin penerimaan limbah medis</span>
                </label>

                <button type="submit" class="px-5 py-2.5 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white font-semibold cursor-pointer">
                    Daftarkan Lokasi TPS Saya
                </button>
            </form>
        </section>
    @endif

@endsection
