<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#133A26">
    <title>Misi Pilah Sampah · Kemedcar</title>
    <meta name="description" content="Belajar memilah sampah medis, limbah B3, elektronik, daur ulang, dan organik melalui kuis singkat.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        :root {
            color-scheme: light;
            --aspal: #1F2421;
            --beton: #F3F5F2;
            --hijau-tua: #133A26;
            --hijau-klinis: #1D6B45;
            --kuning-hazard: #F2C230;
        }
        body {
            margin: 0;
            background: var(--beton);
            color: var(--aspal);
            font-family: 'Public Sans', system-ui, sans-serif;
        }
        h1, h2, h3 { font-family: 'Archivo', system-ui, sans-serif; letter-spacing: -0.02em; }
        .angka-tabular { font-variant-numeric: tabular-nums; }
        button, a { -webkit-tap-highlight-color: transparent; }
        :focus-visible { outline: 3px solid var(--hijau-klinis); outline-offset: 3px; }
        .misi-bg {
            background-image: radial-gradient(ellipse at 10% 10%, rgba(242, 194, 48, .15), transparent 32%),
                radial-gradient(ellipse at 90% 90%, rgba(29, 107, 69, .14), transparent 35%);
        }
        .kategori-pilihan { transition: transform .16s ease, border-color .16s ease, background-color .16s ease; }
        .kategori-pilihan:not(:disabled):hover { transform: translateY(-2px); border-color: #1D6B45; background: #F1F8F3; }
        .kategori-pilihan:disabled { cursor: default; }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">
    <header class="bg-[#133A26] text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="inline-flex items-center rounded-xl">
                <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="w-36 sm:w-44 h-auto rounded bg-white px-2 py-1">
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-sm font-semibold transition-colors">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-sm font-semibold transition-colors">Masuk</a>
            @endauth
        </div>
    </header>

    <main class="misi-bg flex-1">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#1D6B45] hover:underline mb-7">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.56l3.72 3.72a.75.75 0 1 1-1.06 1.06l-5-5a.75.75 0 0 1 0-1.06l5-5a.75.75 0 1 1 1.06 1.06l-3.72 3.72h10.69A.75.75 0 0 1 17 10Z" clip-rule="evenodd"/></svg>
                Kembali ke beranda
            </a>

            <section class="grid lg:grid-cols-[.8fr_1.2fr] gap-8 lg:gap-12 items-center mb-9">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#FFF8DB] border border-[#F2C230] text-[#5A4600] text-xs font-bold uppercase tracking-wide">
                        <span class="w-2 h-2 rounded-full bg-[#1D6B45]" aria-hidden="true"></span>
                        Game edukasi · 10 soal
                    </span>
                    <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold leading-tight text-[#133A26]">Misi Pilah<br class="hidden sm:block"> Sampah</h1>
                    <p class="mt-4 text-base sm:text-lg leading-relaxed text-[#4A554E]">
                        Kenali jalur yang tepat untuk setiap sampah. Jawab 10 soal singkat dan dapatkan penjelasan agar makin bijak memilah.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2" aria-label="Kategori dalam game">
                        <span class="px-3 py-1.5 rounded-lg bg-[#FCE8E6] text-[#8F251E] text-xs font-semibold">Medis</span>
                        <span class="px-3 py-1.5 rounded-lg bg-[#FFF8DB] text-[#665000] text-xs font-semibold">Limbah B3</span>
                        <span class="px-3 py-1.5 rounded-lg bg-[#E8EDF4] text-[#334B6C] text-xs font-semibold">Elektronik</span>
                        <span class="px-3 py-1.5 rounded-lg bg-[#E6F4EC] text-[#155235] text-xs font-semibold">Daur ulang</span>
                        <span class="px-3 py-1.5 rounded-lg bg-[#F3EDE5] text-[#644B2F] text-xs font-semibold">Organik</span>
                    </div>
                </div>

                <div class="relative rounded-3xl bg-[#133A26] text-white p-6 sm:p-8 overflow-hidden shadow-xl">
                    <div class="absolute -right-10 -top-14 w-48 h-48 rounded-full border-[24px] border-white/5" aria-hidden="true"></div>
                    <div class="relative">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-xs uppercase tracking-[.16em] font-bold text-emerald-200">Tantangan hari ini</span>
                                <h2 class="mt-2 text-2xl sm:text-3xl font-bold">Pilah dengan aman</h2>
                            </div>
                            <span class="w-12 h-12 rounded-2xl bg-[#F2C230] text-[#133A26] flex items-center justify-center text-2xl" aria-hidden="true">♻</span>
                        </div>
                        <p class="mt-3 text-sm leading-relaxed text-emerald-50/85">Pilih kategori yang sesuai untuk setiap benda. Tidak ada batas waktu—utamakan memahami cara penanganannya.</p>
                        <div class="mt-6 pt-5 border-t border-white/15 grid grid-cols-2 gap-4 text-sm">
                            <div><span class="block text-2xl font-extrabold text-[#F2C230]">10</span><span class="text-emerald-100/75">benda untuk dipilah</span></div>
                            <div><span class="block text-2xl font-extrabold text-[#F2C230]">5</span><span class="text-emerald-100/75">kategori sampah</span></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-2xl sm:rounded-3xl border border-[#D6DDD7] shadow-sm overflow-hidden" aria-label="Permainan Misi Pilah Sampah">
                <div id="intro-game" class="p-6 sm:p-10 text-center">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-[#E6F4EC] text-[#1D6B45] flex items-center justify-center">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m7 19-4-4 4-4M3 15h11a4 4 0 0 0 4-4V5M17 5l4 4-4 4"/>
                        </svg>
                    </div>
                    <h2 class="mt-5 text-2xl font-bold text-[#1F2421]">Siap menjalankan misi?</h2>
                    <p class="mt-2 max-w-xl mx-auto text-sm sm:text-base leading-relaxed text-[#535E57]">Setiap jawaban disertai tips penanganan. Ingat, limbah medis dan B3 harus diserahkan ke fasilitas berizin, bukan tempat sampah biasa.</p>
                    <button id="mulai-game" type="button" class="mt-6 inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[#1D6B45] hover:bg-[#155235] text-white font-bold shadow-sm transition-colors">
                        Mulai Misi
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                    </button>
                </div>

                <div id="arena-game" class="hidden p-5 sm:p-9">
                    <div class="flex items-center justify-between gap-4 mb-3">
                        <p id="nomor-soal" class="text-sm font-bold text-[#1F2421]"></p>
                        <p class="text-sm text-[#535E57]">Skor: <strong id="skor-game" class="text-[#1D6B45] angka-tabular">0</strong></p>
                    </div>
                    <div class="w-full h-2 rounded-full bg-[#E8EDE9] overflow-hidden" role="progressbar" aria-label="Progres permainan" aria-valuemin="0" aria-valuemax="10" aria-valuenow="0">
                        <div id="progres-game" class="h-full rounded-full bg-[#1D6B45] transition-all duration-300" style="width: 0%"></div>
                    </div>

                    <div id="soal-game" class="mt-8" aria-live="polite">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#1D6B45]">Ke mana benda ini harus disalurkan?</p>
                        <div class="mt-3 flex flex-col sm:flex-row sm:items-center gap-4 sm:gap-6">
                            <div id="ikon-benda" class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-2xl bg-[#FFF8DB] flex items-center justify-center text-5xl" aria-hidden="true"></div>
                            <h2 id="nama-benda" tabindex="-1" class="text-2xl sm:text-3xl font-bold text-[#1F2421]"></h2>
                        </div>
                        <div id="pilihan-game" class="mt-7 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"></div>
                        <div id="umpan-balik" class="hidden mt-5 p-4 rounded-xl text-sm leading-relaxed" role="status" aria-live="polite"></div>
                        <div class="mt-5 flex justify-end">
                            <button id="lanjut-game" type="button" class="hidden px-5 py-2.5 rounded-xl bg-[#133A26] hover:bg-[#1D6B45] text-white font-semibold transition-colors">Soal berikutnya</button>
                        </div>
                    </div>
                </div>

                <div id="hasil-game" class="hidden p-6 sm:p-10 text-center" aria-live="polite">
                    <span id="ikon-hasil" class="mx-auto w-16 h-16 rounded-2xl bg-[#FFF8DB] text-4xl flex items-center justify-center" aria-hidden="true">🌱</span>
                    <p class="mt-5 text-xs font-bold uppercase tracking-wider text-[#1D6B45]">Misi selesai</p>
                    <h2 id="judul-hasil" tabindex="-1" class="mt-2 text-3xl font-extrabold text-[#133A26]"></h2>
                    <p id="pesan-hasil" class="mt-2 text-[#535E57]"></p>
                    <p class="mt-5 text-5xl font-extrabold text-[#1D6B45] angka-tabular"><span id="skor-akhir">0</span><span class="text-xl text-[#69756D]"> / 10</span></p>
                    <button id="ulang-game" type="button" class="mt-7 px-6 py-3 rounded-xl bg-[#1D6B45] hover:bg-[#155235] text-white font-bold transition-colors">Main Lagi</button>
                </div>
            </section>

            <p class="mt-5 text-xs sm:text-sm leading-relaxed text-[#535E57]">
                <strong>Catatan keselamatan:</strong> Game ini untuk edukasi dan bukan pengganti SOP. Jangan menyentuh langsung benda tajam, limbah infeksius, atau bahan berbahaya. Kemasan dan jalur serah harus mengikuti ketentuan fasilitas berizin setempat.
            </p>
        </div>
    </main>

    <footer class="bg-white border-t border-[#D6DDD7]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[#535E57]">
            <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="w-32 h-auto">
            <a href="{{ url('/') }}" class="font-semibold text-[#1D6B45] hover:underline">Kembali ke beranda</a>
        </div>
    </footer>

    <script>
        (function () {
            const soal = [
                { benda: 'Botol plastik kosong dan bersih', ikon: '🧴', kategori: 'Daur ulang', penjelasan: 'Botol plastik yang sudah kosong dan bersih dapat dipilah sebagai anorganik untuk didaur ulang sesuai layanan setempat.' },
                { benda: 'Kulit pisang dari dapur', ikon: '🍌', kategori: 'Organik', penjelasan: 'Sisa buah termasuk sampah organik. Pisahkan dari kemasan agar dapat diolah, misalnya menjadi kompos.' },
                { benda: 'Baterai remote yang sudah habis', ikon: '🔋', kategori: 'Limbah B3', penjelasan: 'Baterai bekas berpotensi mengandung bahan berbahaya. Simpan terpisah dalam kondisi kering dan serahkan ke titik pengumpulan B3.' },
                { benda: 'Jarum suntik bekas', ikon: '⚠️', kategori: 'Medis', penjelasan: 'Benda tajam bekas pakai berisiko melukai dan menularkan infeksi. Jangan dipegang atau dibuang ke sampah biasa; gunakan wadah tahan tusuk oleh petugas yang berwenang.' },
                { benda: 'Ponsel rusak yang tidak terpakai', ikon: '📱', kategori: 'Elektronik', penjelasan: 'Perangkat elektronik perlu jalur pengumpulan e-waste agar komponen dan baterainya ditangani dengan benar.' },
                { benda: 'Kardus kering dan bersih', ikon: '📦', kategori: 'Daur ulang', penjelasan: 'Kardus kering termasuk kertas anorganik yang dapat didaur ulang. Lipat dan jaga tetap kering sebelum disalurkan.' },
                { benda: 'Obat kedaluwarsa', ikon: '💊', kategori: 'Medis', penjelasan: 'Jangan buang obat ke saluran air atau sampah biasa. Simpan terpisah dalam kemasan aman dan tanyakan jalur penyerahan ke fasilitas berizin.' },
                { benda: 'Lampu neon bekas', ikon: '💡', kategori: 'Limbah B3', penjelasan: 'Lampu neon dapat mengandung bahan berbahaya. Jangan pecahkan atau tangani pecahannya sendiri; kemas dengan aman dan serahkan ke pengelola berizin.' },
                { benda: 'Masker dari perawatan pasien infeksius', ikon: '😷', kategori: 'Medis', penjelasan: 'Masker terkontaminasi termasuk limbah medis infeksius. Pisahkan dalam kemasan biohazard dan serahkan melalui jalur fasilitas berizin.' },
                { benda: 'Kaleng minuman aluminium kosong', ikon: '🥫', kategori: 'Daur ulang', penjelasan: 'Kaleng aluminium yang kosong dapat dipilah untuk daur ulang. Pastikan tidak ada sisa cairan dan ikuti ketentuan pengumpulan setempat.' }
            ];
            const kategori = ['Medis', 'Limbah B3', 'Elektronik', 'Daur ulang', 'Organik'];
            const intro = document.getElementById('intro-game');
            const arena = document.getElementById('arena-game');
            const hasil = document.getElementById('hasil-game');
            const tombolMulai = document.getElementById('mulai-game');
            const tombolLanjut = document.getElementById('lanjut-game');
            const tombolUlang = document.getElementById('ulang-game');
            const pilihan = document.getElementById('pilihan-game');
            const umpanBalik = document.getElementById('umpan-balik');
            const progres = document.getElementById('progres-game');
            const barProgres = document.querySelector('[role="progressbar"]');
            let indeks = 0;
            let skor = 0;

            function renderSoal() {
                const item = soal[indeks];
                document.getElementById('nomor-soal').textContent = 'Soal ' + (indeks + 1) + ' dari ' + soal.length;
                document.getElementById('skor-game').textContent = skor;
                document.getElementById('ikon-benda').textContent = item.ikon;
                document.getElementById('nama-benda').textContent = item.benda;
                progres.style.width = (indeks / soal.length * 100) + '%';
                barProgres.setAttribute('aria-valuenow', String(indeks));
                umpanBalik.className = 'hidden mt-5 p-4 rounded-xl text-sm leading-relaxed';
                umpanBalik.textContent = '';
                tombolLanjut.classList.add('hidden');
                pilihan.replaceChildren();

                kategori.forEach(function (namaKategori) {
                    const tombol = document.createElement('button');
                    tombol.type = 'button';
                    tombol.className = 'kategori-pilihan min-h-14 p-4 rounded-xl border border-[#D6DDD7] bg-[#F9FAF9] text-left font-semibold text-[#1F2421]';
                    tombol.textContent = namaKategori;
                    tombol.addEventListener('click', function () {
                        jawab(namaKategori);
                    });
                    pilihan.appendChild(tombol);
                });
            }

            function jawab(jawaban) {
                const item = soal[indeks];
                const benar = jawaban === item.kategori;
                if (benar) skor++;

                Array.from(pilihan.children).forEach(function (tombol) {
                    tombol.disabled = true;
                    if (tombol.textContent === item.kategori) {
                        tombol.classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-900');
                    } else if (tombol.textContent === jawaban && !benar) {
                        tombol.classList.add('border-red-300', 'bg-red-50', 'text-red-900');
                    }
                });

                umpanBalik.className = 'mt-5 p-4 rounded-xl text-sm leading-relaxed ' + (benar
                    ? 'bg-emerald-50 border border-emerald-200 text-emerald-950'
                    : 'bg-amber-50 border border-amber-200 text-amber-950');
                umpanBalik.textContent = (benar ? 'Benar! ' : 'Belum tepat. Kategori yang sesuai: ' + item.kategori + '. ') + item.penjelasan;
                document.getElementById('skor-game').textContent = skor;
                tombolLanjut.textContent = indeks === soal.length - 1 ? 'Lihat Hasil' : 'Soal berikutnya';
                tombolLanjut.classList.remove('hidden');
                tombolLanjut.focus();
            }

            function mulai() {
                indeks = 0;
                skor = 0;
                intro.classList.add('hidden');
                hasil.classList.add('hidden');
                arena.classList.remove('hidden');
                renderSoal();
                document.getElementById('nama-benda').focus();
            }

            tombolMulai.addEventListener('click', mulai);
            tombolLanjut.addEventListener('click', function () {
                indeks++;
                if (indeks < soal.length) {
                    renderSoal();
                    document.getElementById('nama-benda').focus();
                    return;
                }

                arena.classList.add('hidden');
                hasil.classList.remove('hidden');
                progres.style.width = '100%';
                barProgres.setAttribute('aria-valuenow', String(soal.length));
                document.getElementById('skor-akhir').textContent = skor;
                document.getElementById('judul-hasil').textContent = skor === soal.length
                    ? 'Pemilah andal!'
                    : skor >= 7 ? 'Kerja bagus!' : 'Terus belajar!';
                document.getElementById('pesan-hasil').textContent = skor === soal.length
                    ? 'Semua jawaban tepat. Kebiasaan memilah dengan aman dimulai dari kamu.'
                    : 'Kamu menjawab ' + skor + ' dari ' + soal.length + ' soal dengan benar. Main lagi untuk mengingat jalur pemilahan yang tepat.';
                document.getElementById('judul-hasil').focus();
            });
            tombolUlang.addEventListener('click', mulai);
        })();
    </script>
</body>
</html>
