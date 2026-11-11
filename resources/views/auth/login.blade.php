@extends('layouts.akun')

@section('title', 'Masuk')

@section('body')
<main class="akun">
    <section class="akun__form">
        <div class="akun__isi">
            @include('auth.partials.merek')

            <h1>Masuk ke akun</h1>
            <p class="pengantar">Pakai email atau nomor HP yang Anda daftarkan.</p>

            @if (session('status'))
                <p class="pesan pesan--sukses" role="status">{{ session('status') }}</p>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="isian">
                    <label for="login">Email atau nomor HP</label>
                    <input id="login" name="login" type="text" value="{{ old('login') }}"
                           autocomplete="username" required
                           placeholder="nama@email.com atau 0812…"
                           @error('login') aria-invalid="true" aria-describedby="login-galat" @enderror>
                    @error('login')
                        <p class="galat" id="login-galat" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="isian">
                    <div class="sandi-bungkus">
                        <input id="password" name="password" type="password"
                               autocomplete="current-password" required
                               @error('password') aria-invalid="true" aria-describedby="password-galat" @enderror>
                        <button type="button" class="sandi-lihat" data-toggle-password="password"
                                aria-label="Tampilkan kata sandi" aria-pressed="false">
                            <span class="sandi-lihat__teks">Lihat</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="galat" id="password-galat" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="baris-antara">
                    <label class="centang">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <button type="submit" class="tombol">Masuk</button>
            </form>

            <p class="alih">
                Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai penghasil/pengguna, pengelola fasilitas berizin, atau kurir B3</a>
            </p>
        </div>
    </section>

    @include('auth.partials.nota', [
        'judul' => 'Setiap manifes limbah medis & B3 ditimbang resmi di fasilitas berizin.',
        'keterangan' => 'Riwayat manifes pemusnahan medis, status penjemputan kurir, dan poin insentif B3 dapat dipantau setelah masuk.',
    ])
</main>

<dialog id="dialog-lupa-sandi" class="dialog-bantuan" aria-labelledby="judul-lupa-sandi">
    <div class="dialog-bantuan__isi">
        <h2 id="judul-lupa-sandi" class="dialog-bantuan__judul">Bantuan Atur Ulang Kata Sandi</h2>
        <p class="dialog-bantuan__teks">
            Untuk menjaga keamanan akses manifes limbah medis &amp; B3, pengaturan ulang kata sandi diverifikasi melalui administrator sistem atau petugas fasilitas TPS tempat Anda terdaftar.
        </p>
        <ul class="dialog-bantuan__daftar">
            <li>Siapkan <strong>email</strong> atau <strong>nomor HP (WhatsApp)</strong> yang terdaftar pada akun Anda.</li>
            <li>Hubungi layanan bantuan K3 di <strong>0812-0000-0001</strong> atau email <strong>admin@example.com</strong>.</li>
        </ul>
        <div class="dialog-bantuan__aksi">
            <button type="button" id="tutup-lupa-sandi" class="tombol">Mengerti &amp; Tutup</button>
        </div>
    </div>
</dialog>
@endsection
