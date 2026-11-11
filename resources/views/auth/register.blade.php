@extends('layouts.akun')

@section('title', 'Daftar')

@php
    $peranTerpilih = old('role', 'pengguna');
    $daftarPeran = [
        'pengguna' => ['Penghasil / Pengguna', 'Salurkan sampah medis (infeksius, benda tajam, obat) & limbah B3 ke fasilitas berizin. Akun langsung aktif.'],
        'pengelola_tps' => ['Pengelola Fasilitas / TPS', 'Kelola 1 fasilitas penampungan berizin medis/B3 & timbang manifes masuk.'],
        'kurir' => ['Kurir Angkut Medis & B3', 'Jemput kemasan limbah medis/B3 bersegel dan serahkan ke fasilitas tujuan.'],
    ];
@endphp

@section('body')
<main class="akun">
    <section class="akun__form">
        <div class="akun__isi akun__isi--lebar">
            @include('auth.partials.merek')

            <h1>Buat akun</h1>
            <p class="pengantar">Pilih peran Anda dulu. Data yang diminta menyesuaikan peran.</p>

            @if ($errors->any())
                <p class="pesan pesan--galat" role="alert">Ada {{ $errors->count() }} isian yang perlu diperbaiki.</p>
            @endif

            <form method="POST" action="{{ route('register') }}" novalidate>
                @csrf

                <fieldset class="peran">
                    <legend class="legend">Saya mendaftar sebagai</legend>
                    <div class="peran__daftar">
                        @foreach ($daftarPeran as $nilai => [$nama, $ket])
                            <label class="peran__opsi">
                                <input type="radio" name="role" value="{{ $nilai }}" @checked($peranTerpilih === $nilai)>
                                <span class="peran__nama">{{ $nama }}</span>
                                <span class="peran__ket">{{ $ket }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('role')
                        <p class="galat" role="alert">{{ $message }}</p>
                    @enderror
                </fieldset>

                <p class="catatan-verifikasi" role="note">
                    Akun pengelola fasilitas/TPS dan kurir angkut B3 aktif setelah izinnya diverifikasi admin. Anda akan bisa masuk begitu disetujui.
                </p>

                <div class="isian">
                    <label for="nama">Nama lengkap</label>
                    <input id="nama" name="nama" type="text" value="{{ old('nama') }}" maxlength="100"
                           autocomplete="name" required
                           @error('nama') aria-invalid="true" aria-describedby="nama-galat" @enderror>
                    @error('nama')
                        <p class="galat" id="nama-galat" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="baris-2">
                    <div class="isian">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="120"
                               autocomplete="email" required
                               @error('email') aria-invalid="true" aria-describedby="email-galat" @enderror>
                        @error('email')
                            <p class="galat" id="email-galat" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="isian">
                        <label for="no_hp">Nomor HP (WhatsApp)</label>
                        <input id="no_hp" name="no_hp" type="tel" value="{{ old('no_hp') }}" maxlength="20"
                               autocomplete="tel" inputmode="tel" placeholder="081234567890" required
                               aria-describedby="no_hp-bantuan @error('no_hp') no_hp-galat @enderror"
                               @error('no_hp') aria-invalid="true" @enderror>
                        <p class="bantuan" id="no_hp-bantuan">Untuk notifikasi penjemputan.</p>
                        @error('no_hp')
                            <p class="galat" id="no_hp-galat" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="baris-2">
                    <div class="isian">
                        <label for="password">Kata sandi</label>
                        <div class="sandi-bungkus">
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                   aria-describedby="password-bantuan @error('password') password-galat @enderror"
                                   @error('password') aria-invalid="true" @enderror>
                            <button type="button" class="sandi-lihat" data-toggle-password="password"
                                    aria-label="Tampilkan kata sandi" aria-pressed="false">
                                <span class="sandi-lihat__teks">Lihat</span>
                            </button>
                        </div>
                        <p class="bantuan" id="password-bantuan">Minimal 8 karakter.</p>
                        @error('password')
                            <p class="galat" id="password-galat" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="isian">
                        <label for="password_confirmation">Ulangi kata sandi</label>
                        <div class="sandi-bungkus">
                            <input id="password_confirmation" name="password_confirmation" type="password"
                                   autocomplete="new-password" required>
                            <button type="button" class="sandi-lihat" data-toggle-password="password_confirmation"
                                    aria-label="Tampilkan konfirmasi kata sandi" aria-pressed="false">
                                <span class="sandi-lihat__teks">Lihat</span>
                            </button>
                        </div>
                    </div>
                </div>

                <fieldset class="khusus-kurir">
                    <legend class="legend">Data kendaraan kurir</legend>

                    <div class="baris-2">
                        <div class="isian">
                            <label for="jenis_kendaraan">Jenis kendaraan</label>
                            <select id="jenis_kendaraan" name="jenis_kendaraan"
                                    @error('jenis_kendaraan') aria-invalid="true" aria-describedby="jenis_kendaraan-galat" @enderror>
                                <option value="">Pilih kendaraan</option>
                                @foreach (['Motor', 'Motor roda tiga', 'Mobil pikap', 'Truk kecil'] as $kendaraan)
                                    <option value="{{ $kendaraan }}" @selected(old('jenis_kendaraan') === $kendaraan)>{{ $kendaraan }}</option>
                                @endforeach
                            </select>
                            @error('jenis_kendaraan')
                                <p class="galat" id="jenis_kendaraan-galat" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="isian">
                            <label for="plat_nomor">Plat nomor</label>
                            <input id="plat_nomor" name="plat_nomor" type="text" value="{{ old('plat_nomor') }}"
                                   maxlength="15" placeholder="AG 1234 XY" autocapitalize="characters"
                                   @error('plat_nomor') aria-invalid="true" aria-describedby="plat_nomor-galat" @enderror>
                            @error('plat_nomor')
                                <p class="galat" id="plat_nomor-galat" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="baris-2">
                        <div class="isian">
                            <label for="no_sim">Nomor SIM <span class="opsional">(opsional)</span></label>
                            <input id="no_sim" name="no_sim" type="text" value="{{ old('no_sim') }}" maxlength="30"
                                   @error('no_sim') aria-invalid="true" aria-describedby="no_sim-galat" @enderror>
                            @error('no_sim')
                                <p class="galat" id="no_sim-galat" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="isian">
                            <label for="kapasitas_angkut_kg">Kapasitas angkut, kg <span class="opsional">(opsional)</span></label>
                            <input id="kapasitas_angkut_kg" name="kapasitas_angkut_kg" type="number" min="1" step="0.5"
                                   value="{{ old('kapasitas_angkut_kg') }}" inputmode="decimal" placeholder="50"
                                   @error('kapasitas_angkut_kg') aria-invalid="true" aria-describedby="kapasitas-galat" @enderror>
                            @error('kapasitas_angkut_kg')
                                <p class="galat" id="kapasitas-galat" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <button type="submit" class="tombol">Buat akun</button>
            </form>

            <p class="alih">
                Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
            </p>
        </div>
    </section>

    @include('auth.partials.nota', [
        'judul' => 'Kemas limbah medis & B3 sesuai standar K3, salurkan ke fasilitas berizin terdekat.',
        'keterangan' => 'Limbah medis wajib melalui fasilitas berizin resmi. Setiap setoran yang telah ditimbang tercatat digital beserta poin insentifnya.',
    ])
</main>
@endsection
