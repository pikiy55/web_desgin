<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#133A26">
    <title>Kemedcar — Pengelolaan Medis &amp; Limbah B3</title>
    <meta name="description" content="Sistem pengelolaan sampah medis (infeksius, benda tajam, farmasi, patologis) dan limbah B3 (Bahan Berbahaya dan Beracun) terintegrasi dengan fasilitas penampungan berizin resmi.">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='14' fill='%23133A26'/%3E%3Cpath d='M32 18 14 48h36L32 18Z' fill='none' stroke='%23F2C230' stroke-width='5' stroke-linejoin='round'/%3E%3Cpath d='M32 29v8' stroke='%23F2C230' stroke-width='5' stroke-linecap='round'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700;800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    @if($kontenEdukasi->isNotEmpty())
        {{-- StPageFlip (pembalik halaman buku). Alternatif: npm i page-flip lalu import di resources/js/app.js --}}
        <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js" defer></script>
    @endif

    {{--
        Aturan konsistensi visual:
        - Kartu / panel besar : rounded-2xl
        - Tombol & input      : rounded-xl
        - Badge & label       : rounded-lg
        - Panel bersarang di dalam kartu : rounded-xl
        Ukuran teks minimum 12px (text-xs); teks bacaan 14px ke atas.
    --}}
    <style>
        :root {
            --aspal: #1F2421;
            --beton: #F3F5F2;
            --hijau-tua: #133A26;
            --hijau-klinis: #1D6B45;
            --kuning-hazard: #F2C230;
            --merah-medis: #B3261E;
        }
        body {
            font-family: 'Public Sans', system-ui, -apple-system, sans-serif;
            background-color: #F3F5F2;
            color: #1F2421;
        }
        h1, h2, h3, h4, .font-display {
            font-family: 'Archivo', system-ui, -apple-system, sans-serif;
            letter-spacing: -0.02em;
        }
        .angka-tabular { font-variant-numeric: tabular-nums; }

        /* Indikator fokus yang konsisten untuk semua elemen interaktif */
        a:focus-visible, button:focus-visible, summary:focus-visible, [tabindex]:focus-visible {
            outline: 3px solid #1D6B45;
            outline-offset: 2px;
        }
        .on-dark a:focus-visible, .on-dark button:focus-visible, .on-dark summary:focus-visible,
        .on-dark [tabindex]:focus-visible, a.on-dark:focus-visible, button.on-dark:focus-visible {
            outline-color: #F2C230;
        }

        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .ikon-buka { transform: rotate(180deg); }

        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }

        /* ---------- Buku panduan K3 (StPageFlip) & Reader Design ---------- */
        [data-edukasi-idx][aria-expanded="true"] {
            border-color: #1D6B45;
            background-color: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(29, 107, 69, 0.25);
        }
        #ruang-buku {
            transition: all 0.3s ease-in-out;
            background: radial-gradient(120% 120% at 50% 0%, #17422C 0%, #0D281A 60%, #081C11 100%);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        .halaman-buku {
            background-color: #FDFBF7;
            color: #1F2421;
            overflow: hidden;
            box-sizing: border-box;
            user-select: none;
            -webkit-user-select: none;
        }
        .halaman-isi {
            position: relative;
            height: 100%;
            padding: 22px 20px 24px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        /* Header halaman buku */
        .halaman-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #E8EDE9;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6C7A72;
        }
        .halaman-header .header-kanan {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 55%;
            font-weight: 500;
            color: #8C9991;
        }
        /* Konten halaman & pembagi teks */
        .teks-buku, .ukur-buku {
            font-family: 'Public Sans', system-ui, -apple-system, sans-serif;
            font-size: 13.5px;
            line-height: 1.65;
            color: #242B26;
            flex: 1 1 auto;
            margin-top: 8px;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        .ukur-buku {
            overflow: hidden;
        }
        @media (min-width: 640px) {
            .halaman-isi {
                padding: 24px 22px 28px;
            }
            .teks-buku, .ukur-buku {
                font-size: 14.5px;
                line-height: 1.72;
                margin-top: 10px;
            }
        }
        .teks-buku p, .ukur-buku p {
            margin: 0 0 0.8em;
            overflow-wrap: anywhere;
        }
        .teks-buku p:last-child, .ukur-buku p:last-child {
            margin-bottom: 0;
        }
        /* Kotak ringkasan SOP */
        .ringkasan-buku {
            margin: 0 0 0.9em;
            padding: 10px 12px;
            border-left: 4px solid #1D6B45;
            border-radius: 10px;
            background: #EDF5F0;
            color: #17422C;
            font-size: 13px;
            font-weight: 500;
            line-height: 1.55;
        }
        /* Footer halaman buku */
        .halaman-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 8px;
            border-top: 1px solid #E8EDE9;
            font-size: 11px;
            color: #6C7A72;
            font-variant-numeric: tabular-nums;
        }
        .halaman-footer .footer-nomor {
            font-weight: 700;
            color: #1F2421;
        }
        /* Efek lipatan punggung buku (spine crease shadow) */
        .stf__item.--left .halaman-isi {
            box-shadow: inset -12px 0 16px -8px rgba(0, 0, 0, 0.12);
        }
        .stf__item.--right .halaman-isi {
            box-shadow: inset 12px 0 16px -8px rgba(0, 0, 0, 0.12);
        }
        /* Sampul Buku Hardcover */
        .sampul-depan, .sampul-belakang {
            background: #0F2F1E;
            color: #FFFFFF;
        }
        .sampul-depan {
            background: linear-gradient(135deg, #0A2215 0%, #133A26 50%, #0C2819 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sampul-belakang {
            background: linear-gradient(225deg, #0A2215 0%, #133A26 50%, #0C2819 100%);
            border-left: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sampul-isi {
            height: 100%;
            padding: 24px 20px 22px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }
        @media (min-width: 640px) {
            .sampul-isi {
                padding: 28px 24px 24px;
            }
        }
        .sampul-depan .sampul-isi {
            border-left: 10px solid #081B11;
            box-shadow: inset 8px 0 12px -4px rgba(0,0,0,0.6);
        }
        .sampul-belakang .sampul-isi {
            border-right: 10px solid #081B11;
            box-shadow: inset -8px 0 12px -4px rgba(0,0,0,0.6);
        }
        .sampul-badge {
            align-self: flex-start;
            padding: 4px 10px;
            border-radius: 8px;
            background: #F2C230;
            color: #1F2421;
            font-family: 'Archivo', system-ui, sans-serif;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .sampul-judul {
            margin: 10px 0 6px;
            font-family: 'Archivo', system-ui, sans-serif;
            font-size: 21px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.02em;
            color: #FFFFFF;
            overflow-wrap: anywhere;
        }
        @media (min-width: 640px) {
            .sampul-judul { font-size: 25px; }
        }
        .sampul-sub {
            font-size: 12px;
            color: #A3BFB0;
            line-height: 1.5;
            margin: 0;
        }
        .sampul-tanggal {
            font-size: 12px;
            color: #D5E6DB;
            font-variant-numeric: tabular-nums;
        }
        .sampul-garis {
            height: 10px;
            border-radius: 4px;
            background: repeating-linear-gradient(-45deg, #F2C230 0 10px, #1F2421 10px 20px);
            margin: 10px 0;
        }
        .sampul-merek {
            margin: 0;
            font-size: 11.5px;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: #D5E6DB;
            text-transform: uppercase;
        }
        .stf__parent {
            position: relative !important;
            margin: 0 auto !important;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-[#1D6B45] selection:text-white">

    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:px-4 focus:py-2 focus:rounded-xl focus:bg-white focus:text-[#1F2421] focus:font-semibold">
        Lewati ke konten utama
    </a>

    {{-- Pita Status Pengawasan Limbah Medis & B3 --}}
    <div class="on-dark bg-[#133A26] text-[#E6EFE9] text-xs border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-[#F2C230]" aria-hidden="true"></span>
                <span>Pengawasan terpadu limbah medis &amp; limbah B3</span>
            </div>
            <div class="flex items-center gap-3 angka-tabular text-[#D5E6DB]">
                <span><strong>{{ $ringkasan['total_tps_medis'] ?? 1 }}</strong> fasilitas berizin medis</span>
                <span aria-hidden="true" class="hidden sm:inline">·</span>
                <span class="hidden sm:inline">Tertangani: <strong>{{ number_format((float) ($ringkasan['total_berat_selesai_kg'] ?? 0), 2, ',', '.') }} kg</strong></span>
            </div>
        </div>
    </div>

    {{-- Navigasi Utama --}}
    <header class="sticky top-0 z-40 bg-[#F3F5F2]/95 backdrop-blur-md border-b border-[#D6DDD7]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-3">
            <a href="{{ url('/') }}" class="inline-flex items-center text-[#1F2421] rounded-xl py-1">
                <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="w-32 sm:w-44 md:w-48 h-auto">
            </a>

            {{-- Menu desktop --}}
            <nav aria-label="Navigasi bagian halaman" class="hidden md:flex items-center gap-6 text-sm font-medium text-[#37413B]">
                <a href="#standar-kemasan" class="hover:text-[#1D6B45] transition-colors py-1">SOP Kemasan</a>
                <a href="#katalog-limbah" class="hover:text-[#1D6B45] transition-colors py-1">Jenis Limbah</a>
                <a href="#fasilitas-berizin" class="hover:text-[#1D6B45] transition-colors py-1">Fasilitas Berizin</a>
                <a href="#data-nasional" class="hover:text-[#1D6B45] transition-colors py-1">Data Nasional</a>
                <a href="{{ route('game.pilah') }}" class="hover:text-[#1D6B45] transition-colors py-1">Game Pilah</a>
                @if($kontenEdukasi->isNotEmpty())
                    <a href="#edukasi" class="hover:text-[#1D6B45] transition-colors py-1">Panduan K3</a>
                @endif
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-3.5 py-2 rounded-xl text-sm font-semibold text-[#1F2421] hover:bg-[#E4EAE5] transition-colors">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-[#1D6B45] hover:bg-[#155235] text-white text-sm font-semibold transition-colors">
                    <span class="sm:hidden">Daftar</span><span class="hidden sm:inline">Daftar Akun</span>
                </a>

                {{-- Tombol hamburger (mobile) --}}
                <button type="button" id="tombol-menu"
                        class="md:hidden w-10 h-10 rounded-xl border border-[#C6D1C9] bg-white text-[#1F2421] flex items-center justify-center"
                        aria-expanded="false" aria-controls="menu-mobile" aria-label="Buka menu navigasi">
                    <svg id="ikon-menu-buka" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg id="ikon-menu-tutup" class="w-5 h-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>

        {{-- Panel menu mobile --}}
        <nav id="menu-mobile" aria-label="Navigasi bagian halaman (ponsel)" class="hidden md:hidden border-t border-[#D6DDD7] bg-[#F3F5F2]">
            <ul class="max-w-7xl mx-auto px-4 sm:px-6 py-2 text-base font-medium text-[#1F2421]">
                <li><a href="#standar-kemasan" class="block py-3 border-b border-[#E2E8E4]">SOP Kemasan</a></li>
                <li><a href="#katalog-limbah" class="block py-3 border-b border-[#E2E8E4]">Jenis Limbah</a></li>
                <li><a href="#fasilitas-berizin" class="block py-3 border-b border-[#E2E8E4]">Fasilitas Berizin</a></li>
                <li><a href="#data-nasional" class="block py-3 border-b border-[#E2E8E4]">Data Nasional</a></li>
                <li><a href="{{ route('game.pilah') }}" class="block py-3 border-b border-[#E2E8E4]">Game Pilah Sampah</a></li>
                @if($kontenEdukasi->isNotEmpty())
                    <li><a href="#edukasi" class="block py-3 border-b border-[#E2E8E4]">Panduan K3</a></li>
                @endif
                <li class="sm:hidden"><a href="{{ route('login') }}" class="block py-3 font-semibold text-[#1D6B45]">Masuk ke akun</a></li>
            </ul>
        </nav>
    </header>

    <main id="konten-utama" class="flex-1">
        {{-- HERO --}}
        <section class="relative overflow-hidden border-b border-[#D6DDD7] bg-gradient-to-b from-[#EBF0EC] to-[#F3F5F2] py-10 sm:py-14 lg:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    <div class="lg:col-span-7 space-y-6">
                        <div class="inline-flex flex-wrap items-center gap-2 px-3 py-1.5 rounded-lg bg-[#FCE8E6] border border-[#F2B8B5] text-[#B3261E] text-xs font-bold">
                            <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                            </svg>
                            <span>Wajib diserahkan ke fasilitas berizin limbah medis &amp; B3</span>
                        </div>

                        <h1 class="font-display text-3xl sm:text-4xl lg:text-[2.7rem] font-extrabold text-[#1F2421] leading-[1.12]">
                            Kelola sampah medis &amp; limbah B3 secara aman, terlacak, dan tersertifikasi.
                        </h1>

                        <p class="text-base sm:text-lg text-[#454F49] leading-relaxed max-w-2xl">
                            Jangan buang jarum suntik, masker infeksius, obat kedaluwarsa, baterai litium, atau lampu merkuri ke tempat sampah biasa. Salurkan ke <strong>fasilitas penampungan berizin</strong> lewat penjemputan kurir khusus atau antar sendiri, lengkap dengan manifes timbangan digital dan pelacakan status pemusnahan.
                        </p>

                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-[#1D6B45] hover:bg-[#155235] text-white font-semibold text-base shadow-sm transition-colors">
                                <span>Masuk ke Dashboard</span>
                                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-5 py-3.5 rounded-xl bg-white hover:bg-[#E4EAE5] text-[#1F2421] border border-[#C6D1C9] font-semibold text-base transition-colors">
                                Daftar Akun Baru
                            </a>
                            <a href="{{ route('game.pilah') }}" class="inline-flex items-center gap-2 px-5 py-3.5 rounded-xl text-[#1D6B45] hover:bg-[#E4EAE5] border border-[#A9D4BB] font-semibold text-base transition-colors">
                                Coba Game Pilah
                            </a>
                        </div>
                    </div>

                    {{-- Ilustrasi wadah berkode warna --}}
                    <div class="lg:col-span-5">
                        <div class="bg-white rounded-2xl border border-[#D6DDD7] p-5 sm:p-6 shadow-sm">
                            <p class="text-xs font-bold uppercase tracking-wider text-[#4A554E] mb-4">Wadah yang benar untuk tiap limbah</p>
                            <div class="grid grid-cols-2 gap-3">
                                <figure class="rounded-xl bg-[#FFF8DB] border border-[#F2C230] p-4 text-center">
                                    <svg viewBox="0 0 64 64" class="w-16 h-16 mx-auto" aria-hidden="true">
                                        <path d="M18 22c0-4 4-6 14-6s14 2 14 6l4 28c.4 3-2 6-5 6H19c-3 0-5.4-3-5-6l4-28Z" fill="#F2C230" stroke="#8A6A00" stroke-width="2"/>
                                        <path d="M24 16c0-5 3-9 8-9s8 4 8 9" fill="none" stroke="#8A6A00" stroke-width="3" stroke-linecap="round"/>
                                        <circle cx="32" cy="38" r="7" fill="none" stroke="#1F2421" stroke-width="2.5"/>
                                        <path d="M32 31v14M26 35l12 6M38 35l-12 6" stroke="#1F2421" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <figcaption class="mt-2 text-sm font-bold text-[#1F2421]">Kantong kuning</figcaption>
                                    <span class="text-xs text-[#4A554E]">Limbah infeksius</span>
                                </figure>
                                <figure class="rounded-xl bg-[#ECEEED] border border-[#B9C0BB] p-4 text-center">
                                    <svg viewBox="0 0 64 64" class="w-16 h-16 mx-auto" aria-hidden="true">
                                        <rect x="12" y="18" width="40" height="38" rx="5" fill="#F2C230" stroke="#8A6A00" stroke-width="2"/>
                                        <rect x="12" y="10" width="40" height="11" rx="3" fill="#1F2421"/>
                                        <rect x="24" y="14" width="16" height="3" rx="1.5" fill="#F2C230"/>
                                        <path d="M32 28v16" stroke="#1F2421" stroke-width="3" stroke-linecap="round"/>
                                        <path d="M32 48l-3-5h6l-3 5Z" fill="#1F2421"/>
                                    </svg>
                                    <figcaption class="mt-2 text-sm font-bold text-[#1F2421]">Safety box</figcaption>
                                    <span class="text-xs text-[#4A554E]">Jarum &amp; benda tajam</span>
                                </figure>
                                <figure class="rounded-xl bg-[#F6EBDD] border border-[#D9B98F] p-4 text-center">
                                    <svg viewBox="0 0 64 64" class="w-16 h-16 mx-auto" aria-hidden="true">
                                        <rect x="22" y="8" width="20" height="8" rx="2" fill="#6B4423"/>
                                        <path d="M24 16h16v6c4 3 8 6 8 14v14c0 4-3 6-6 6H22c-3 0-6-2-6-6V36c0-8 4-11 8-14v-6Z" fill="#9A6A3A"/>
                                        <rect x="24" y="32" width="16" height="12" rx="2" fill="#F6EBDD"/>
                                        <path d="M30 35v6M27 38h6" stroke="#6B4423" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <figcaption class="mt-2 text-sm font-bold text-[#1F2421]">Kemasan cokelat</figcaption>
                                    <span class="text-xs text-[#4A554E]">Obat &amp; bahan kimia</span>
                                </figure>
                                <figure class="rounded-xl bg-[#E6F4EC] border border-[#A9D4BB] p-4 text-center">
                                    <svg viewBox="0 0 64 64" class="w-16 h-16 mx-auto" aria-hidden="true">
                                        <rect x="16" y="14" width="32" height="42" rx="4" fill="#1D6B45"/>
                                        <rect x="13" y="8" width="38" height="9" rx="3" fill="#133A26"/>
                                        <path d="M24 30h16M24 38h16M24 46h16" stroke="#E6F4EC" stroke-width="3" stroke-linecap="round"/>
                                    </svg>
                                    <figcaption class="mt-2 text-sm font-bold text-[#1F2421]">Kontainer tertutup</figcaption>
                                    <span class="text-xs text-[#4A554E]">Baterai &amp; e-waste</span>
                                </figure>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3 peran pengguna --}}
                <div class="mt-10 pt-8 border-t border-[#D6DDD7] grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white/90 rounded-2xl p-5 border border-[#D6DDD7]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#B3261E]">Penghasil / Warga</span>
                            <span class="text-xs font-semibold text-[#4A554E]">Klinik &amp; rumah</span>
                        </div>
                        <p class="text-sm text-[#454F49] leading-relaxed">Segel limbah infeksius, benda tajam, dan farmasi terpisah. Pantau status angkut hingga selesai.</p>
                    </div>
                    <div class="bg-white/90 rounded-2xl p-5 border border-[#D6DDD7]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#133A26]">Fasilitas Berizin</span>
                            <span class="text-xs font-semibold text-[#4A554E]">1 akun · 1 unit</span>
                        </div>
                        <p class="text-sm text-[#454F49] leading-relaxed">Pastikan izin pengelolaan limbah medis aktif, timbang berat aktual, dan jaga kuota penyimpanan tetap aman.</p>
                    </div>
                    <div class="bg-white/90 rounded-2xl p-5 border border-[#D6DDD7]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#854D0E]">Kurir Khusus B3</span>
                            <span class="text-xs font-semibold text-[#4A554E]">Bukti foto</span>
                        </div>
                        <p class="text-sm text-[#454F49] leading-relaxed">Jemput kemasan tertutup dari lokasi, unggah bukti pengangkutan, dan serahkan ke fasilitas tujuan.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- STANDAR KEMASAN: fokus pada aturan teknis (jenis limbahnya sudah digambarkan di hero) --}}
        <section id="standar-kemasan" class="on-dark scroll-mt-20 py-12 sm:py-14 bg-[#1F2421] text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#F2C230] block mb-1">Protokol K3 &amp; pemilahan sumber</span>
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-white">
                            Aturan pengemasan sebelum penyerahan
                        </h2>
                    </div>
                    <p class="text-sm text-[#D5DBD7] max-w-md">
                        Kurir maupun fasilitas berizin berhak menolak setoran bila kemasan bocor atau benda tajam tidak berada di wadah tahan tusuk.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="rounded-2xl bg-[#2A302C] border border-white/10 p-5 flex flex-col gap-4">
                        <div>
                            <span class="inline-block px-2.5 py-1 mb-3 rounded-lg bg-[#F2C230] text-[#1F2421] font-display font-extrabold text-xs uppercase">Kantong kuning</span>
                            <h3 class="font-display font-bold text-base text-white mb-2">Infeksius</h3>
                            <ul class="space-y-1.5 text-sm text-[#D5DBD7] leading-relaxed list-disc pl-4 marker:text-[#F2C230]">
                                <li>Isi maksimal 3/4 kantong, lalu ikat rapat.</li>
                                <li>Jangan dipadatkan atau ditekan dengan tangan.</li>
                                <li>Kantong bocor atau robek akan ditolak.</li>
                            </ul>
                        </div>
                        <div class="mt-auto pt-3 border-t border-white/10 text-xs text-[#F2C230] font-semibold">Wajib izin limbah medis</div>
                    </div>

                    <div class="rounded-2xl bg-[#2A302C] border border-[#F2C230]/40 p-5 flex flex-col gap-4">
                        <div>
                            <span class="inline-block px-2.5 py-1 mb-3 rounded-lg bg-[#F2C230] text-[#1F2421] font-display font-extrabold text-xs uppercase">Safety box</span>
                            <h3 class="font-display font-bold text-base text-white mb-2">Benda tajam</h3>
                            <ul class="space-y-1.5 text-sm text-[#D5DBD7] leading-relaxed list-disc pl-4 marker:text-[#F2C230]">
                                <li>Wadah keras, tahan tusuk, dan anti-bocor.</li>
                                <li>Jangan mematahkan atau menutup ulang jarum.</li>
                                <li>Tutup rapat saat terisi 3/4, jangan dibuka lagi.</li>
                            </ul>
                        </div>
                        <div class="mt-auto pt-3 border-t border-white/10 text-xs text-[#F2C230] font-semibold">Tanpa wadah tahan tusuk = ditolak</div>
                    </div>

                    <div class="rounded-2xl bg-[#2A302C] border border-white/10 p-5 flex flex-col gap-4">
                        <div>
                            <span class="inline-block px-2.5 py-1 mb-3 rounded-lg bg-[#92400E] text-white font-display font-extrabold text-xs uppercase">Kemasan cokelat</span>
                            <h3 class="font-display font-bold text-base text-white mb-2">Farmasi &amp; kimia</h3>
                            <ul class="space-y-1.5 text-sm text-[#D5DBD7] leading-relaxed list-disc pl-4 marker:text-[#FDE68A]">
                                <li>Botol dan blister dalam keadaan tertutup.</li>
                                <li>Jangan buang sisa cairan ke saluran air.</li>
                                <li>Jangan dicampur dengan sampah domestik.</li>
                            </ul>
                        </div>
                        <div class="mt-auto pt-3 border-t border-white/10 text-xs text-[#FDE68A] font-semibold">Pisahkan dari sampah rumah tangga</div>
                    </div>

                    <div class="rounded-2xl bg-[#2A302C] border border-white/10 p-5 flex flex-col gap-4">
                        <div>
                            <span class="inline-block px-2.5 py-1 mb-3 rounded-lg bg-[#1D6B45] text-white font-display font-extrabold text-xs uppercase">Kontainer B3</span>
                            <h3 class="font-display font-bold text-base text-white mb-2">B3 &amp; elektronik</h3>
                            <ul class="space-y-1.5 text-sm text-[#D5DBD7] leading-relaxed list-disc pl-4 marker:text-[#86EFAC]">
                                <li>Lampu neon dibungkus kardus agar tidak pecah.</li>
                                <li>Baterai dan aki ditaruh tegak, tidak bocor.</li>
                                <li>Elektronik rusak dimasukkan utuh, jangan dibongkar.</li>
                            </ul>
                        </div>
                        <div class="mt-auto pt-3 border-t border-white/10 text-xs text-[#86EFAC] font-semibold">Dapat poin setoran per kg</div>
                    </div>
                </div>

                <p class="mt-6 text-xs text-[#B9C3BD] max-w-3xl">
                    Warna wadah mengikuti praktik umum fasilitas pelayanan kesehatan di Indonesia. Ikuti juga ketentuan fasilitas penerima yang Anda pilih, karena label dan jenis wadah dapat berbeda antar-fasilitas.
                </p>
            </div>
        </section>

        {{-- KATALOG JENIS LIMBAH --}}
        @php
            $grupKat = ['medis' => 'medis', 'b3' => 'b3', 'elektronik' => 'ewaste', 'anorganik' => 'daur', 'organik' => 'daur'];
            $labelGrup = ['medis' => 'Limbah Medis', 'b3' => 'Limbah B3', 'ewaste' => 'E-Waste', 'daur' => 'Daur Ulang'];

            $jumlahGrup = [];
            foreach ($jenisSampah as $js) {
                $g = $grupKat[$js->kategori] ?? 'daur';
                $jumlahGrup[$g] = ($jumlahGrup[$g] ?? 0) + 1;
            }

            // Tab hanya ditampilkan untuk kategori yang benar-benar punya data
            $daftarTab = ['semua' => 'Semua (' . $jenisSampah->count() . ')'];
            foreach ($labelGrup as $kodeGrup => $namaGrup) {
                if (! empty($jumlahGrup[$kodeGrup])) {
                    $daftarTab[$kodeGrup] = $namaGrup . ' (' . $jumlahGrup[$kodeGrup] . ')';
                }
            }
            $tampilkanTab = count($daftarTab) > 2;

            $badgeMap = [
                'medis'      => ['bg' => 'bg-[#FCE8E6]', 'text' => 'text-[#B3261E]', 'label' => 'Limbah medis'],
                'b3'         => ['bg' => 'bg-[#FEF3C7]', 'text' => 'text-[#854D0E]', 'label' => 'Limbah B3'],
                'elektronik' => ['bg' => 'bg-[#E8F1FA]', 'text' => 'text-[#164C78]', 'label' => 'E-waste'],
                'anorganik'  => ['bg' => 'bg-[#E6F4EC]', 'text' => 'text-[#135433]', 'label' => 'Daur ulang anorganik'],
                'organik'    => ['bg' => 'bg-[#F1F5E6]', 'text' => 'text-[#3F5E14]', 'label' => 'Organik terpilah'],
            ];
        @endphp
        <section id="katalog-limbah" class="scroll-mt-20 py-14 sm:py-16 border-b border-[#D6DDD7] bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-[#B3261E] block mb-1">Katalog klasifikasi limbah</span>
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#1F2421]">
                            Jenis limbah yang diterima &amp; ketentuannya
                        </h2>
                        <p class="text-base text-[#535E57] mt-2 max-w-2xl">
                            Limbah <strong>medis</strong> hanya dimusnahkan oleh fasilitas berizin (tanpa poin). Limbah B3, elektronik, dan daur ulang memberi poin insentif per item yang diserahkan.
                        </p>
                    </div>

                    @if($tampilkanTab)
                        <div role="tablist" aria-label="Filter kategori limbah" id="daftar-tab-limbah" class="flex flex-wrap gap-2">
                            @foreach($daftarTab as $kodeTab => $labelTab)
                                <button type="button" role="tab"
                                        id="tab-{{ $kodeTab }}"
                                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-controls="panel-limbah"
                                        tabindex="{{ $loop->first ? '0' : '-1' }}"
                                        data-filter-btn="{{ $kodeTab }}"
                                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors border {{ $loop->first ? 'bg-[#133A26] text-white border-[#133A26]' : 'bg-[#F3F5F2] text-[#37413B] border-[#D6DDD7] hover:bg-[#E4EAE5]' }}">
                                    {{ $labelTab }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div id="panel-limbah" @if($tampilkanTab) role="tabpanel" aria-labelledby="tab-semua" @endif class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($jenisSampah as $jenis)
                        @php
                            $katInfo = $badgeMap[$jenis->kategori] ?? $badgeMap['anorganik'];
                            $grupCard = $grupKat[$jenis->kategori] ?? 'daur';
                        @endphp
                        <article
                            data-kategori-card="{{ $grupCard }}"
                            class="rounded-2xl border {{ $jenis->kategori === 'medis' ? 'border-[#F2B8B5] bg-[#FFFBFB]' : ($jenis->kategori === 'b3' ? 'border-[#FDE68A] bg-[#FFFDF5]' : 'border-[#D6DDD7] bg-[#F9FAF9]') }} p-5 flex flex-col justify-between gap-4">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $katInfo['bg'] }} {{ $katInfo['text'] }}">
                                        {{ $katInfo['label'] }}
                                    </span>
                                    @if($jenis->poin_per_kg > 0)
                                        <span class="inline-flex items-baseline gap-1 px-2.5 py-1 rounded-lg bg-[#133A26] text-white font-display font-bold text-sm angka-tabular shrink-0">
                                            +{{ $jenis->poin_per_kg }} <span class="text-xs font-normal text-[#D5E6DB]">poin/item</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[#B3261E] text-white font-semibold text-xs shrink-0">
                                            Pemusnahan aman
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-display font-bold text-base text-[#1F2421] mb-1">{{ $jenis->nama }}</h3>
                                <p class="text-sm text-[#535E57] leading-relaxed">
                                    {{ $jenis->deskripsi ?: 'Dikemas terpisah sesuai protokol penanganan limbah.' }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-[#E2E8E4] text-sm">
                                @if($jenis->kategori === 'medis')
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#B3261E]">
                                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                                        <span>Wajib fasilitas berizin limbah medis</span>
                                    </span>
                                @elseif($jenis->perlu_penanganan_khusus)
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#854D0E]">
                                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd"/></svg>
                                        <span>Penanganan khusus: kemasan kedap</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-[#1D6B45] font-medium">
                                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/></svg>
                                        <span>Diterima di seluruh fasilitas terverifikasi</span>
                                    </span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- FASILITAS BERIZIN --}}
        <section id="fasilitas-berizin" class="scroll-mt-20 py-14 sm:py-16 border-b border-[#D6DDD7] bg-[#F3F5F2]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-8 max-w-3xl">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#1D6B45] block mb-1">Rekomendasi &amp; kuota penyimpanan</span>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#1F2421]">
                        Fasilitas penampungan berizin limbah medis &amp; B3
                    </h2>
                    <p class="text-base text-[#535E57] mt-2">
                        Sistem rekomendasi fasilitas memprioritaskan lokasi terdekat yang memiliki izin pengelolaan limbah medis aktif dan ruang penyimpanan yang cukup.
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    @forelse($daftarTps as $tps)
                        @php
                            $totalKg = max(1, (float) $tps->kapasitas_total_kg);
                            $terpakaiKg = (float) $tps->kapasitas_terpakai_kg;
                            $sisaKg = max(0, $totalKg - $terpakaiKg);
                            $persen = min(100, round(($terpakaiKg / $totalKg) * 100, 1));
                            $warnaBar = $persen >= 90 ? 'bg-[#B3261E]' : ($persen >= 75 ? 'bg-[#B45309]' : 'bg-[#1D6B45]');
                        @endphp
                        <div class="bg-white rounded-2xl border {{ $tps->izin_limbah_medis ? 'border-[#1D6B45]' : 'border-[#D6DDD7]' }} p-6 flex flex-col justify-between gap-5">
                            <div>
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        @if($tps->status === 'buka')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#E6F4EC] text-[#135433] text-xs font-bold uppercase">
                                                <span class="w-2 h-2 rounded-full bg-[#1D6B45]" aria-hidden="true"></span>
                                                Menerima manifes
                                            </span>
                                        @elseif($tps->status === 'penuh')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[#FCE8E6] text-[#B3261E] text-xs font-bold uppercase">Kuota penuh</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[#E4EAE5] text-[#454F49] text-xs font-bold uppercase">Tutup</span>
                                        @endif

                                        @if($tps->izin_limbah_medis)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[#FCE8E6] text-[#B3261E] border border-[#F2B8B5] text-xs font-bold">
                                                Berizin limbah medis &amp; B3
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-[#F3F5F2] text-[#4A554E] text-xs font-medium">
                                                Non-medis (B3 rumah tangga &amp; daur ulang)
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-[#4A554E] angka-tabular">No. izin: {{ $tps->no_izin ?: 'Terverifikasi' }}</span>
                                </div>

                                <h3 class="font-display text-xl font-bold text-[#1F2421]">{{ $tps->nama_tps }}</h3>
                                <p class="text-sm text-[#535E57] mt-1">{{ $tps->alamat }}</p>
                            </div>

                            <div class="bg-[#F9FAF9] rounded-xl p-4 border border-[#E2E8E4] space-y-2.5">
                                <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-sm angka-tabular">
                                    <span class="font-semibold text-[#37413B]">Terisi {{ number_format($terpakaiKg, 2, ',', '.') }} kg ({{ $persen }}%)</span>
                                    <span class="font-bold text-[#1D6B45]">Tersedia {{ number_format($sisaKg, 2, ',', '.') }} kg</span>
                                </div>
                                <div class="w-full h-2.5 bg-[#E2E8E4] rounded-full overflow-hidden" role="progressbar" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100" aria-label="Kapasitas terpakai {{ $tps->nama_tps }}">
                                    <div class="h-full {{ $warnaBar }} rounded-full" style="width: {{ max(3, $persen) }}%"></div>
                                </div>
                                <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-xs text-[#4A554E] angka-tabular">
                                    <span>Jam penerimaan: <strong>{{ substr((string) $tps->jam_buka, 0, 5) }} – {{ substr((string) $tps->jam_tutup, 0, 5) }} WIB</strong></span>
                                    <span>Total kuota: <strong>{{ number_format($totalKg, 0, ',', '.') }} kg</strong></span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="lg:col-span-2 bg-white rounded-2xl border border-[#D6DDD7] p-8 text-center text-sm text-[#535E57]">
                            Belum ada fasilitas berizin yang terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- DATA NASIONAL --}}
        {{-- Angka dapat diganti dari controller lewat variabel $dataNasional dengan kunci yang sama seperti default di bawah. --}}
        @php
            $dn = $dataNasional ?? [
                'tahun'              => 2025,
                'total_fasyankes'    => 146751,
                'lapor_speed'        => 10958,
                'belum_lapor'        => 135793,
                'potensi_ton'        => 190656.61,
                'tercatat_ton'       => 60457.45,
                'belum_terlapor_ton' => 130199.16,
            ];

            $pctLapor      = round($dn['lapor_speed'] / $dn['total_fasyankes'] * 100, 2);
            $pctBelumLapor = round($dn['belum_lapor'] / $dn['total_fasyankes'] * 100, 2);
            $pctTercatat   = round($dn['tercatat_ton'] / $dn['potensi_ton'] * 100, 2);
            $pctBelumTon   = round($dn['belum_terlapor_ton'] / $dn['potensi_ton'] * 100, 2);

            $fmt   = fn ($n, $d = 0) => number_format((float) $n, $d, ',', '.');
            $lebar = fn ($n) => number_format((float) $n, 2, '.', '');

            // Komposisi limbah fasyankes (Kemenkes, 2017)
            $komposisi = [
                ['label' => 'Limbah domestik (non-medis)', 'persen' => 80, 'warna' => '#6B766F'],
                ['label' => 'Infeksius & patologis',       'persen' => 15, 'warna' => '#B3261E'],
                ['label' => 'Kimia & farmasi',             'persen' => 3,  'warna' => '#B45309'],
                ['label' => 'Termometer & tabung',         'persen' => 1,  'warna' => '#0369A1'],
                ['label' => 'Benda tajam',                 'persen' => 1,  'warna' => '#7A1712'],
            ];

            // Kapasitas pengolahan limbah medis per hari (UGM / Kemenkes, 2019)
            $kapasitasOlahan = [
                ['label' => '10 jasa pengolah berizin',         'ton' => 170, 'warna' => '#1D6B45'],
                ['label' => '87 RS dengan insinerator sendiri', 'ton' => 60,  'warna' => '#B45309'],
                ['label' => 'Tidak terkelola',                  'ton' => 74,  'warna' => '#B3261E'],
            ];
            $totalOlahan = array_sum(array_column($kapasitasOlahan, 'ton'));
        @endphp

        <section id="data-nasional" class="scroll-mt-20 py-14 sm:py-16 border-b border-[#D6DDD7] bg-[#EBF0EC]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-8 max-w-3xl">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#B3261E] block mb-1">Data nasional</span>
                    <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#1F2421]">
                        Sebagian besar limbah medis Indonesia belum terpantau
                    </h2>
                </div>

                {{-- Angka utama --}}
                <div class="on-dark bg-[#133A26] text-white rounded-2xl p-6 sm:p-8">
                    <p class="text-sm text-[#D5E6DB]">Pelaporan limbah B3 medis lewat SPEED, {{ $dn['tahun'] }}</p>

                    <div class="mt-2 flex flex-wrap items-end gap-x-4 gap-y-1">
                        <p class="font-display text-5xl sm:text-6xl font-extrabold text-[#F2C230] angka-tabular leading-none">{{ $fmt($pctTercatat, 1) }}%</p>
                        <p class="text-base sm:text-lg text-white max-w-md pb-1">tonase limbah B3 medis yang tercatat di SPEED</p>
                    </div>

                    <div class="mt-5 flex h-3.5 w-full overflow-hidden rounded-full bg-[#0E2B1C]" role="img"
                         aria-label="{{ $fmt($pctTercatat, 2) }} persen tonase tercatat di SPEED, {{ $fmt($pctBelumTon, 2) }} persen belum terlapor atau belum jelas">
                        <div class="h-full bg-[#F2C230]" style="width: {{ $lebar($pctTercatat) }}%"></div>
                        <div class="h-full bg-[#E5584D]" style="width: {{ $lebar($pctBelumTon) }}%"></div>
                    </div>
                    <dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm angka-tabular">
                        <div>
                            <dt class="flex items-center gap-2 text-[#D5E6DB]"><span class="w-3 h-3 rounded-sm bg-[#F2C230]" aria-hidden="true"></span>Tercatat di SPEED</dt>
                            <dd class="font-bold mt-0.5">{{ $fmt($dn['tercatat_ton'], 2) }} ton</dd>
                        </div>
                        <div>
                            <dt class="flex items-center gap-2 text-[#D5E6DB]"><span class="w-3 h-3 rounded-sm bg-[#E5584D]" aria-hidden="true"></span>Belum terlapor / belum jelas</dt>
                            <dd class="font-bold mt-0.5">{{ $fmt($dn['belum_terlapor_ton'], 2) }} ton ({{ $fmt($pctBelumTon, 2) }}%)</dd>
                        </div>
                    </dl>

                    <div class="mt-6 pt-5 border-t border-white/15">
                        <p class="text-base">
                            Hanya <strong class="text-[#F2C230] angka-tabular">{{ $fmt($pctLapor, 2) }}%</strong> fasyankes yang sudah melapor
                            <span class="text-[#D5E6DB] angka-tabular">({{ $fmt($dn['lapor_speed']) }} dari {{ $fmt($dn['total_fasyankes']) }} unit)</span>.
                        </p>
                        <div class="mt-3 flex h-3.5 w-full overflow-hidden rounded-full bg-[#0E2B1C]" role="img"
                             aria-label="{{ $fmt($pctLapor, 2) }} persen fasyankes sudah melapor, {{ $fmt($pctBelumLapor, 2) }} persen belum">
                            <div class="h-full bg-[#F2C230]" style="width: {{ $lebar(max(1.5, $pctLapor)) }}%"></div>
                            <div class="h-full bg-[#E5584D]" style="width: {{ $lebar($pctBelumLapor) }}%"></div>
                        </div>
                        <p class="mt-2 text-sm text-[#D5E6DB] angka-tabular">Belum melapor: {{ $fmt($pctBelumLapor, 2) }}% ({{ $fmt($dn['belum_lapor']) }} unit)</p>
                    </div>

                    <p class="mt-6 text-sm text-[#D5E6DB] leading-relaxed max-w-3xl">
                        Limbah yang belum terlapor belum tentu dibuang sembarangan, tetapi tidak dapat dipastikan bahwa limbah itu diangkut dan dimusnahkan oleh pihak berizin. Sumber: KLH/BPLH, Agustus 2026.
                    </p>
                </div>

                {{-- Konteks tambahan (dilipat agar halaman tidak padat) --}}
                <div class="mt-5 space-y-3">
                    <details class="bg-white rounded-2xl border border-[#D6DDD7]">
                        <summary class="flex items-center justify-between gap-3 p-5 cursor-pointer rounded-2xl">
                            <span class="font-display font-bold text-base text-[#1F2421]">Kapasitas pengolahan belum menutup timbulan</span>
                            <svg class="ikon-buka w-5 h-5 shrink-0 text-[#4A554E] transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </summary>
                        <div class="px-5 pb-5 space-y-4">
                            <p class="text-sm text-[#454F49] leading-relaxed max-w-3xl">
                                Paparan Kemenkes tahun 2019 memperkirakan limbah medis dari sekitar 2.820 rumah sakit dan 9.884 puskesmas mencapai kurang lebih <strong class="angka-tabular">290 ton per hari</strong>, belum termasuk klinik, unit transfusi darah, dan apotek.
                            </p>
                            <ul class="space-y-3">
                                @foreach($kapasitasOlahan as $ko)
                                    <li>
                                        <div class="flex justify-between gap-3 text-sm mb-1 angka-tabular">
                                            <span class="text-[#37413B]">{{ $ko['label'] }}</span>
                                            <span class="font-bold text-[#1F2421] shrink-0">±{{ $ko['ton'] }} ton/hari</span>
                                        </div>
                                        <div class="h-2.5 w-full bg-[#E2E8E4] rounded-full overflow-hidden" aria-hidden="true">
                                            <div class="h-full rounded-full" style="width: {{ $lebar($ko['ton'] / $totalOlahan * 100) }}%; background: {{ $ko['warna'] }}"></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="text-xs text-[#4A554E]">Sumber: Humas UGM (2019), paparan Kemenkes.</p>
                        </div>
                    </details>

                    <details class="bg-white rounded-2xl border border-[#D6DDD7]">
                        <summary class="flex items-center justify-between gap-3 p-5 cursor-pointer rounded-2xl">
                            <span class="font-display font-bold text-base text-[#1F2421]">Hanya sebagian kecil limbah fasyankes yang berbahaya</span>
                            <svg class="ikon-buka w-5 h-5 shrink-0 text-[#4A554E] transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </summary>
                        <div class="px-5 pb-5 space-y-4">
                            <p class="text-sm text-[#454F49] leading-relaxed max-w-3xl">
                                Menurut pengelompokan Kemenkes (data 2017), sebagian besar sampah fasyankes sebenarnya limbah domestik. Pemilahan di sumber menjaga limbah infeksius tidak bercampur dengan sampah biasa.
                            </p>
                            <ul class="space-y-3" aria-label="Komposisi limbah fasyankes">
                                @foreach($komposisi as $k)
                                    <li>
                                        <div class="flex justify-between gap-3 text-sm mb-1 angka-tabular">
                                            <span class="text-[#37413B]">{{ $k['label'] }}</span>
                                            <span class="font-bold text-[#1F2421]">{{ $k['persen'] }}%</span>
                                        </div>
                                        <div class="h-2.5 w-full bg-[#E2E8E4] rounded-full overflow-hidden" aria-hidden="true">
                                            <div class="h-full rounded-full" style="width: {{ $k['persen'] }}%; min-width: 4px; background: {{ $k['warna'] }}"></div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="text-xs text-[#4A554E]">Sumber: Kemenkes (2017), dikutip Mongabay Indonesia (2020).</p>
                        </div>
                    </details>

                    <details class="bg-white rounded-2xl border border-[#D6DDD7]">
                        <summary class="flex items-center justify-between gap-3 p-5 cursor-pointer rounded-2xl">
                            <span class="font-display font-bold text-base text-[#1F2421]">Pandemi menambah beban</span>
                            <svg class="ikon-buka w-5 h-5 shrink-0 text-[#4A554E] transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </summary>
                        <div class="px-5 pb-5 space-y-3">
                            <p class="text-sm text-[#454F49] leading-relaxed max-w-3xl">
                                KLHK mencatat rekapitulasi limbah medis selama pandemi Covid-19 sebesar <strong class="angka-tabular">7.502,79 ton</strong> sejak awal pandemi hingga 9 Februari 2021, berdasarkan laporan yang masuk.
                            </p>
                            <p class="text-sm text-[#454F49] leading-relaxed max-w-3xl">
                                Pada waktu yang sama, <strong class="angka-tabular">124 rumah sakit</strong> tercatat berizin memusnahkan limbah medis: 119 dengan insinerator dan 5 dengan autoklaf. Selama masa darurat, insinerator yang belum berizin sempat diizinkan beroperasi dengan syarat suhu minimal 800 °C dan pengawasan KLHK.
                            </p>
                            <p class="text-xs text-[#4A554E]">Sumber: KLHK, dikutip Republika (16 Februari 2021); Kementerian LHK, dikutip Pajak.com.</p>
                        </div>
                    </details>

                    <details class="bg-white rounded-2xl border border-[#D6DDD7]">
                        <summary class="flex items-center justify-between gap-3 p-5 cursor-pointer rounded-2xl">
                            <span class="font-display font-bold text-base text-[#1F2421]">Dasar aturan &amp; apa artinya bagi penghasil limbah</span>
                            <svg class="ikon-buka w-5 h-5 shrink-0 text-[#4A554E] transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </summary>
                        <div class="px-5 pb-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-display font-bold text-sm text-[#1F2421] mb-2">Dasar aturan</h3>
                                <ul class="space-y-2.5 text-sm text-[#454F49] leading-relaxed">
                                    <li class="pl-3 border-l-4 border-[#1D6B45]"><strong>PP Nomor 22 Tahun 2021</strong> tentang Penyelenggaraan Perlindungan dan Pengelolaan Lingkungan Hidup menjadi dasar kewajiban pengelolaan limbah B3 dari fasyankes.</li>
                                    <li class="pl-3 border-l-4 border-[#1D6B45]">Pelaporan dilakukan melalui <strong>SPEED</strong>, sistem digital KLH/BPLH untuk memantau dan mengevaluasi pengelolaan limbah B3.</li>
                                    <li class="pl-3 border-l-4 border-[#1D6B45]">Pengawasan lingkungan hidup mengacu pada <strong>PermenLH Nomor 6 Tahun 2026</strong>.</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="font-display font-bold text-sm text-[#1F2421] mb-2">Yang perlu Anda lakukan</h3>
                                <ul class="space-y-2.5 text-sm text-[#454F49] leading-relaxed">
                                    <li class="pl-3 border-l-4 border-[#B45309]">Pisahkan limbah infeksius, benda tajam, dan farmasi dari sampah domestik sejak dari sumbernya.</li>
                                    <li class="pl-3 border-l-4 border-[#B45309]">Serahkan hanya ke fasilitas berizin. Manifes timbangan di platform ini membuat setiap kilogram limbah punya jejak yang bisa ditelusuri.</li>
                                    <li class="pl-3 border-l-4 border-[#B45309]">Fasyankes: pastikan unit Anda sudah melapor di SPEED agar limbah Anda ikut terhitung dalam data nasional.</li>
                                </ul>
                            </div>
                        </div>
                    </details>
                </div>

                {{-- Catatan metodologi & sumber --}}
                <div class="mt-8 text-xs text-[#4A554E] leading-relaxed max-w-4xl">
                    <p class="mb-2">
                        <strong>Catatan:</strong> angka antar-tahun berasal dari sumber, cakupan, dan satuan yang berbeda (ton per hari dan ton per tahun), sehingga tidak dapat dibandingkan langsung. Data {{ $dn['tahun'] }} adalah potensi timbulan dan laporan yang masuk ke SPEED, bukan hasil pengukuran seluruh fasyankes.
                    </p>
                    <p class="flex flex-wrap gap-x-4 gap-y-1">
                        <span class="font-semibold">Sumber:</span>
                        <a class="underline hover:text-[#1D6B45]" href="https://www.kemenlh.go.id/news/detail/tingkatkan-kepatuhan-klhbplh-dorong-fasilitas-pelayanan-kesehatan-optimalisasi-pelaporan-limbah-b3" target="_blank" rel="noopener noreferrer">KLH/BPLH, 13 Agustus 2026</a>
                        <a class="underline hover:text-[#1D6B45]" href="https://ugm.ac.id/?p=9575" target="_blank" rel="noopener noreferrer">UGM, 2019</a>
                        <a class="underline hover:text-[#1D6B45]" href="https://news.republika.co.id/berita/qom2oi409/ribuan-ton-limbah-medis-dan-saran-daur-ulang-dari-lipi" target="_blank" rel="noopener noreferrer">Republika, 16 Februari 2021</a>
                        <a class="underline hover:text-[#1D6B45]" href="https://mongabay.co.id/2020/10/05/dampak-limbah-medis-saat-pandemi/" target="_blank" rel="noopener noreferrer">Mongabay Indonesia, 5 Oktober 2020</a>
                    </p>
                </div>
            </div>
        </section>

        {{-- PANDUAN K3 (dari tabel konten): dibaca sebagai buku di dalam halaman (StPageFlip), bukan popup --}}
        @if($kontenEdukasi->isNotEmpty())
            @php
                $dataEdukasi = $kontenEdukasi->map(function ($a) {
                    $halamanList = method_exists($a, 'daftarHalaman')
                        ? $a->daftarHalaman()
                        : array_values(array_filter(array_map('trim', explode('[[halaman-baru]]', (string) ($a->isi ?? $a->ringkasan ?? '')))));
                    if (empty($halamanList)) {
                        $halamanList = [trim((string) ($a->ringkasan ?? ''))];
                    }
                    $isiBersih = method_exists($a, 'isiPolos')
                        ? $a->isiPolos()
                        : trim(str_replace('[[halaman-baru]]', "\n\n", (string) ($a->isi ?? $a->ringkasan ?? '')));

                    return [
                        'judul'     => $a->judul,
                        'jenis'     => $a->jenis,
                        'tanggal'   => $a->published_at ? \Carbon\Carbon::parse($a->published_at)->translatedFormat('d M Y') : '',
                        'ringkasan' => $a->ringkasan ?? '',
                        'isi'       => $isiBersih,
                        'halaman'   => $halamanList,
                    ];
                })->values();
            @endphp

            <section id="edukasi" class="scroll-mt-20 py-14 sm:py-16 bg-white border-b border-[#D6DDD7]">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="mb-8">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#B3261E] block mb-1">Protokol K3 &amp; sosialisasi</span>
                        <h2 class="font-display text-2xl sm:text-3xl font-bold text-[#1F2421]">
                            Panduan penanganan limbah medis &amp; B3
                        </h2>
                        <p class="text-base text-[#535E57] mt-2">Pilih salah satu kartu untuk membuka bukunya.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach($dataEdukasi as $i => $artikel)
                            <button type="button"
                                    data-edukasi-idx="{{ $i }}"
                                    aria-controls="ruang-buku"
                                    aria-expanded="false"
                                    class="group text-left bg-[#F9FAF9] hover:bg-white rounded-2xl border border-[#D6DDD7] hover:border-[#1D6B45] p-5 flex flex-col justify-between gap-4 transition-colors">
                                <span class="block">
                                    <span class="flex items-center justify-between gap-2 mb-3">
                                        <span class="px-2.5 py-1 rounded-lg bg-[#FCE8E6] text-[#B3261E] text-xs font-semibold uppercase">{{ $artikel['jenis'] }}</span>
                                        @if($artikel['tanggal'])
                                            <time class="text-xs text-[#4A554E] angka-tabular">{{ $artikel['tanggal'] }}</time>
                                        @endif
                                    </span>
                                    <span class="block font-display font-bold text-base text-[#1F2421] group-hover:text-[#1D6B45] transition-colors mb-2">{{ $artikel['judul'] }}</span>
                                    <span class="block text-sm text-[#535E57] leading-relaxed line-clamp-3">
                                        {{ $artikel['ringkasan'] ?: \Illuminate\Support\Str::limit($artikel['isi'], 140) }}
                                    </span>
                                </span>
                                <span class="pt-3 border-t border-[#E2E8E4] flex items-center justify-between text-sm font-semibold text-[#1D6B45]">
                                    <span>Buka buku ({{ count($artikel['halaman']) }} halaman)</span>
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 5.5A1.5 1.5 0 0 1 5.5 4H11v15H5.5A1.5 1.5 0 0 0 4 20.5v-15ZM20 5.5A1.5 1.5 0 0 0 18.5 4H13v15h5.5a1.5 1.5 0 0 1 1.5 1.5v-15Z"/></svg>
                                </span>
                            </button>
                        @endforeach
                    </div>

                    {{-- Ruang baca buku interaktif (StPageFlip) --}}
                    <div id="ruang-buku" role="region" aria-label="Pembaca buku panduan K3" tabindex="-1"
                         class="on-dark hidden mt-10 scroll-mt-24 rounded-2xl sm:rounded-3xl p-5 sm:p-7 md:p-8 text-white relative">

                        {{-- Header bilah baca --}}
                        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 mb-4 border-b border-white/15">
                            <div class="min-w-0 max-w-2xl">
                                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                    <span id="buku-jenis" class="px-2.5 py-0.5 rounded-md bg-[#F2C230] text-[#1F2421] text-[11px] font-extrabold uppercase tracking-wider font-display"></span>
                                    <span class="text-xs text-[#A3BFB0] flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-[#F2C230]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L1 21h22L12 2zm0 3.8L20.2 19H3.8L12 5.8zM11 10h2v4h-2zm0 6h2v2h-2z"/></svg>
                                        Buku Saku Kepatuhan SOP &amp; B3
                                    </span>
                                </div>
                                <h3 id="buku-judul" class="font-display text-xl sm:text-2xl font-bold text-white leading-tight"></h3>
                            </div>
                            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                                <span class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 border border-white/15 text-xs text-[#D5E6DB]">
                                    <kbd class="font-mono text-[11px] bg-black/40 px-1.5 py-0.5 rounded border border-white/20">ESC</kbd> tutup
                                </span>
                                <button type="button" id="buku-tutup" aria-label="Tutup buku panduan"
                                        class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/25 text-white text-sm font-semibold transition-all flex items-center gap-2 active:scale-95">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    <span>Tutup</span>
                                </button>
                            </div>
                        </div>

                        {{-- Arena buku dengan floating desktop navigation chevrons --}}
                        <div class="relative flex items-center justify-center my-4 sm:my-6 min-h-[380px] sm:min-h-[480px]">
                            {{-- Tombol navigasi melayang kiri (Desktop) --}}
                            <button type="button" id="buku-nav-prev" aria-label="Balik ke halaman sebelumnya"
                                    class="hidden lg:flex absolute -left-3 xl:-left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-[#133A26]/95 hover:bg-[#1D6B45] text-white border border-white/25 shadow-xl items-center justify-center transition-all disabled:opacity-0 disabled:pointer-events-none active:scale-90">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                            </button>

                            {{-- Wadah utama buku StPageFlip --}}
                            <div id="area-buku" class="flex justify-center items-center w-full overflow-visible"></div>

                            {{-- Tombol navigasi melayang kanan (Desktop) --}}
                            <button type="button" id="buku-nav-next" aria-label="Balik ke halaman berikutnya"
                                    class="hidden lg:flex absolute -right-3 xl:-right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-[#133A26]/95 hover:bg-[#1D6B45] text-white border border-white/25 shadow-xl items-center justify-center transition-all disabled:opacity-0 disabled:pointer-events-none active:scale-90">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>

                        {{-- Bilah kendali bawah & indikator progres baca --}}
                        <div class="mt-4 pt-4 border-t border-white/15 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <button type="button" id="buku-sebelumnya"
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/25 text-white text-sm font-semibold transition-all flex items-center justify-center gap-2 disabled:opacity-30 disabled:cursor-not-allowed active:scale-95">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                <span>Sebelumnya</span>
                            </button>

                            <div class="flex flex-col items-center gap-1.5">
                                <p id="buku-status" aria-live="polite" class="text-sm font-medium text-[#D5E6DB] angka-tabular"></p>
                                <div class="w-32 sm:w-44 h-1.5 bg-white/15 rounded-full overflow-hidden">
                                    <div id="buku-progress-bar" class="h-full bg-[#F2C230] transition-all duration-300 rounded-full" style="width: 0%"></div>
                                </div>
                            </div>

                            <button type="button" id="buku-berikutnya"
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-[#F2C230] hover:bg-[#E0B020] text-[#1F2421] text-sm font-bold transition-all flex items-center justify-center gap-2 disabled:opacity-30 disabled:cursor-not-allowed active:scale-95 shadow-md">
                                <span>Berikutnya</span>
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-[#A3BFB0] text-center">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"/></svg>
                                Klik sudut halaman atau seret untuk membalik
                            </span>
                            <span class="hidden sm:inline">·</span>
                            <span class="hidden sm:flex items-center gap-1">
                                <kbd class="px-1.5 py-0.5 rounded bg-white/10 font-mono text-[10px]">←</kbd>
                                <kbd class="px-1.5 py-0.5 rounded bg-white/10 font-mono text-[10px]">→</kbd>
                                tombol panah keyboard
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <script type="application/json" id="data-edukasi">{!! json_encode($dataEdukasi, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>
        @endif

        {{-- AJAKAN AKHIR --}}
        <section class="on-dark py-14 sm:py-16 bg-[#133A26] text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7 space-y-3">
                        <span class="inline-block px-3 py-1 rounded-lg bg-[#F2C230] text-[#1F2421] font-display font-bold text-xs uppercase tracking-wider">
                            Kepatuhan &amp; keselamatan lingkungan
                        </span>
                        <h2 class="font-display text-2xl sm:text-3xl font-extrabold text-white">
                            Salurkan limbah medis &amp; B3 ke jalur resmi sekarang.
                        </h2>
                        <p class="text-base text-[#D5E6DB] max-w-2xl leading-relaxed">
                            Daftar sebagai <strong>Penghasil / Warga</strong> untuk membuat manifes setoran, <strong>Pengelola Fasilitas</strong> (1 akun mengelola 1 fasilitas berizin), atau <strong>Kurir Penjemputan</strong>.
                        </p>
                    </div>
                    <div class="lg:col-span-5 flex flex-wrap lg:justify-end gap-3">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-[#F2C230] hover:bg-[#E0B020] text-[#1F2421] font-display font-bold text-base transition-colors">
                            Daftar Akun Baru
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/25 font-semibold text-base transition-colors">
                            Masuk ke Akun
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- Footer --}}
    <footer class="on-dark bg-[#1F2421] text-[#D5DBD7] py-10 text-sm border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center">
                <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="w-36 sm:w-44 h-auto rounded bg-white px-2 py-1">
            </div>
            <nav aria-label="Tautan footer" class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <a href="#standar-kemasan" class="hover:text-white transition-colors">SOP Kemasan</a>
                <a href="#katalog-limbah" class="hover:text-white transition-colors">Jenis Limbah</a>
                <a href="#fasilitas-berizin" class="hover:text-white transition-colors">Fasilitas Berizin</a>
                <a href="#data-nasional" class="hover:text-white transition-colors">Data Nasional</a>
            </nav>
        </div>
    </footer>

    <script>
        (function () {
            /* ---------- Menu mobile ---------- */
            const tombolMenu = document.getElementById('tombol-menu');
            const menuMobile = document.getElementById('menu-mobile');
            const ikonBuka = document.getElementById('ikon-menu-buka');
            const ikonTutup = document.getElementById('ikon-menu-tutup');

            function aturMenu(buka) {
                if (!tombolMenu || !menuMobile) return;
                menuMobile.classList.toggle('hidden', !buka);
                ikonBuka.classList.toggle('hidden', buka);
                ikonTutup.classList.toggle('hidden', !buka);
                tombolMenu.setAttribute('aria-expanded', buka ? 'true' : 'false');
                tombolMenu.setAttribute('aria-label', buka ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            }

            if (tombolMenu && menuMobile) {
                tombolMenu.addEventListener('click', () => aturMenu(menuMobile.classList.contains('hidden')));
                menuMobile.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => aturMenu(false)));
                window.matchMedia('(min-width: 768px)').addEventListener('change', (e) => { if (e.matches) aturMenu(false); });
            }

            /* ---------- Tab filter katalog limbah ---------- */
            const tabs = Array.from(document.querySelectorAll('[data-filter-btn]'));
            const kartu = Array.from(document.querySelectorAll('[data-kategori-card]'));
            const panel = document.getElementById('panel-limbah');

            const gayaAktif = ['bg-[#133A26]', 'text-white', 'border-[#133A26]'];
            const gayaNonaktif = ['bg-[#F3F5F2]', 'text-[#37413B]', 'border-[#D6DDD7]', 'hover:bg-[#E4EAE5]'];

            function pilihTab(tab, fokus) {
                const kode = tab.getAttribute('data-filter-btn');
                tabs.forEach((t) => {
                    const aktif = t === tab;
                    t.setAttribute('aria-selected', aktif ? 'true' : 'false');
                    t.setAttribute('tabindex', aktif ? '0' : '-1');
                    gayaAktif.forEach((c) => t.classList.toggle(c, aktif));
                    gayaNonaktif.forEach((c) => t.classList.toggle(c, !aktif));
                });
                if (panel) panel.setAttribute('aria-labelledby', tab.id);
                kartu.forEach((k) => {
                    const cocok = kode === 'semua' || k.getAttribute('data-kategori-card') === kode;
                    k.classList.toggle('hidden', !cocok);
                });
                if (fokus) tab.focus();
            }

            tabs.forEach((tab, i) => {
                tab.addEventListener('click', () => pilihTab(tab, false));
                tab.addEventListener('keydown', (e) => {
                    let target = null;
                    if (e.key === 'ArrowRight') target = tabs[(i + 1) % tabs.length];
                    if (e.key === 'ArrowLeft') target = tabs[(i - 1 + tabs.length) % tabs.length];
                    if (e.key === 'Home') target = tabs[0];
                    if (e.key === 'End') target = tabs[tabs.length - 1];
                    if (target) { e.preventDefault(); pilihTab(target, true); }
                });
            });

            /* ---------- Buku panduan K3 (StPageFlip) ---------- */
            const ruang = document.getElementById('ruang-buku');
            const sumberData = document.getElementById('data-edukasi');
            if (!ruang || !sumberData) return;

            const dataEdukasi = JSON.parse(sumberData.textContent);
            const areaBuku = document.getElementById('area-buku');
            const elJudul = document.getElementById('buku-judul');
            const elJenis = document.getElementById('buku-jenis');
            const elStatus = document.getElementById('buku-status');
            const elProgress = document.getElementById('buku-progress-bar');
            const tombolSebelumnya = document.getElementById('buku-sebelumnya');
            const tombolBerikutnya = document.getElementById('buku-berikutnya');
            const tombolNavPrev = document.getElementById('buku-nav-prev');
            const tombolNavNext = document.getElementById('buku-nav-next');
            const tombolTutupBuku = document.getElementById('buku-tutup');
            const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            let buku = null;
            let pemicu = null;
            let idxAktif = -1;
            let ukuranAktif = null;
            let tokenBuka = 0;
            let timerUkuran = null;

            function buatEl(tag, kelas, teks) {
                const e = document.createElement(tag);
                if (kelas) e.className = kelas;
                if (teks !== undefined) e.textContent = teks;
                return e;
            }

            // Menghitung ukuran halaman yang adaptif untuk ponsel, tablet, dan desktop
            function ukuranHalaman() {
                const paddingW = window.innerWidth < 640 ? 40 : 80;
                const lebarWadah = areaBuku.clientWidth || Math.min(window.innerWidth - paddingW, 960);
                const tinggiLayar = window.innerHeight || 800;

                // Mode 2 halaman (spread) aktif jika lebar panggung buku >= 800px
                const dua = lebarWadah >= 800;

                let w, h;
                if (dua) {
                    const batasLebar = Math.min(lebarWadah - 40, 860);
                    w = Math.floor(batasLebar / 2);
                    w = Math.max(Math.min(w, 420), 290);
                    const batasTinggi = Math.min(Math.floor(tinggiLayar * 0.70), 620);
                    h = Math.min(Math.round(w * 1.38), Math.max(batasTinggi, 440));
                } else {
                    const batasLebar = Math.min(lebarWadah - 16, 420);
                    w = Math.max(Math.min(batasLebar, 390), 280);
                    const batasTinggi = Math.min(Math.floor(tinggiLayar * 0.62), 560);
                    h = Math.min(Math.round(w * 1.40), Math.max(batasTinggi, 400));
                }

                return { w: Math.round(w), h: Math.round(h), dua: dua };
            }

            // Menyusun halaman buku berdasarkan daftar halaman dari admin (atau membagi otomatis bila belum diatur).
            function bagiHalaman(d, w, h) {
                const adaRingkasan = !!(d.ringkasan && d.ringkasan !== d.isi);

                if (Array.isArray(d.halaman) && d.halaman.length > 0) {
                    return d.halaman.map((teksHalaman, idx) => {
                        const par = String(teksHalaman || '')
                            .split(/\n+/)
                            .map((s) => s.trim())
                            .filter(Boolean);
                        return {
                            ringkasan: idx === 0 && adaRingkasan,
                            paragraf: par.length ? par : ['']
                        };
                    });
                }

                const paragraf = String(d.isi || d.ringkasan || '').split(/\n+/).map((s) => s.trim()).filter(Boolean);

                const ukur = buatEl('div', 'ukur-buku');
                ukur.style.cssText = 'position:absolute;left:-9999px;top:0;visibility:hidden;overflow:hidden;display:flow-root;'
                    + 'width:' + (w - 44) + 'px;height:' + (h - 114) + 'px;';
                document.body.appendChild(ukur);

                const hasil = [];
                let isi = [];
                const muat = () => ukur.scrollHeight <= ukur.clientHeight;

                function mulai() {
                    ukur.innerHTML = '';
                    isi = [];
                    if (hasil.length === 0 && adaRingkasan) ukur.appendChild(buatEl('div', 'ringkasan-buku', d.ringkasan));
                }
                function tutupHalaman() {
                    hasil.push({ ringkasan: hasil.length === 0 && adaRingkasan, paragraf: isi });
                    mulai();
                }

                mulai();
                paragraf.forEach((par) => {
                    const utuh = buatEl('p', '', par);
                    ukur.appendChild(utuh);
                    if (muat()) { isi.push(par); return; }
                    ukur.removeChild(utuh);

                    let sisa = par.split(/\s+/).filter(Boolean);
                    while (sisa.length) {
                        const p = buatEl('p');
                        ukur.appendChild(p);
                        let n = 0;
                        while (n < sisa.length) {
                            p.textContent = sisa.slice(0, n + 1).join(' ');
                            if (!muat()) break;
                            n++;
                        }
                        if (n === 0) {
                            ukur.removeChild(p);
                            if (isi.length === 0) isi.push(sisa.shift());
                            tutupHalaman();
                            continue;
                        }
                        p.textContent = sisa.slice(0, n).join(' ');
                        isi.push(sisa.slice(0, n).join(' '));
                        sisa = sisa.slice(n);
                        if (sisa.length) tutupHalaman();
                    }
                });
                if (isi.length || hasil.length === 0) tutupHalaman();

                document.body.removeChild(ukur);
                return hasil;
            }

            function buatSampulDepan(d) {
                const halaman = buatEl('div', 'halaman-buku sampul-depan');
                halaman.dataset.density = 'hard';
                const dalam = buatEl('div', 'sampul-isi');

                // Header sampul
                const header = buatEl('div', 'flex items-center justify-between gap-2');
                header.appendChild(buatEl('span', 'sampul-badge', d.jenis || 'EDUKASI'));
                header.appendChild(buatEl('span', 'text-[10px] font-bold text-[#F2C230] uppercase tracking-wider', 'SOP RESMI K3'));
                dalam.appendChild(header);

                // Konten tengah sampul
                const tengah = buatEl('div', 'my-auto');
                const lambang = buatEl('div', 'w-10 h-10 mb-3 rounded-lg bg-[#F2C230]/15 border border-[#F2C230]/30 flex items-center justify-center text-[#F2C230]');
                lambang.innerHTML = '<svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>';
                tengah.appendChild(lambang);
                tengah.appendChild(buatEl('h4', 'sampul-judul', d.judul));
                tengah.appendChild(buatEl('p', 'sampul-sub', 'Pedoman teknis pemilahan, penampungan berizin & kepatuhan limbah medis dan B3.'));
                dalam.appendChild(tengah);

                // Footer sampul
                const bawah = buatEl('div', 'mt-auto');
                if (d.tanggal) bawah.appendChild(buatEl('time', 'sampul-tanggal block mb-1', 'Diterbitkan: ' + d.tanggal));
                bawah.appendChild(buatEl('div', 'sampul-garis'));
                bawah.appendChild(buatEl('p', 'sampul-merek', 'Kemedcar'));
                dalam.appendChild(bawah);

                halaman.appendChild(dalam);
                return halaman;
            }

            function buatSampulBelakang() {
                const halaman = buatEl('div', 'halaman-buku sampul-belakang');
                halaman.dataset.density = 'hard';
                const dalam = buatEl('div', 'sampul-isi');

                const header = buatEl('div', 'flex items-center justify-between gap-2');
                header.appendChild(buatEl('span', 'sampul-badge', 'LEMBAR PENUTUP'));
                dalam.appendChild(header);

                const tengah = buatEl('div', 'my-auto text-left');
                const badge = buatEl('div', 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-white/10 text-xs text-[#D5E6DB] mb-3');
                badge.innerHTML = '<svg class="w-3.5 h-3.5 text-[#F2C230]" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg> <span>Kepatuhan UU No. 32/2009</span>';
                tengah.appendChild(badge);
                tengah.appendChild(buatEl('h4', 'sampul-judul text-lg sm:text-xl', 'Salurkan Limbah ke Fasilitas & Penampungan Berizin'));
                tengah.appendChild(buatEl('p', 'sampul-sub mt-2', 'Jangan buang limbah medis atau B3 sembarangan. Pastikan setiap penyerahan tercatat pada manifes timbangan resmi.'));
                dalam.appendChild(tengah);

                const bawah = buatEl('div', 'mt-auto');
                bawah.appendChild(buatEl('div', 'sampul-garis'));
                bawah.appendChild(buatEl('p', 'sampul-merek', 'Bank Sampah & Drop Point Terpadu'));
                dalam.appendChild(bawah);

                halaman.appendChild(dalam);
                return halaman;
            }

            function buatHalamanIsi(info, nomor, totalHalaman, d) {
                const halaman = buatEl('div', 'halaman-buku');
                halaman.dataset.density = 'soft';
                const dalam = buatEl('div', 'halaman-isi');

                // Running Header
                const header = buatEl('div', 'halaman-header');
                header.appendChild(buatEl('span', 'header-kiri', (d.jenis || 'PANDUAN') + ' · K3 RESMI'));
                header.appendChild(buatEl('span', 'header-kanan', d.judul));
                dalam.appendChild(header);

                // Body
                const teks = buatEl('div', 'teks-buku');
                if (info.ringkasan && d.ringkasan) {
                    const ringkasanBox = buatEl('div', 'ringkasan-buku');
                    ringkasanBox.innerHTML = '<strong class="block text-xs uppercase tracking-wider text-[#1D6B45] mb-1">Intisari Panduan:</strong>' + d.ringkasan;
                    teks.appendChild(ringkasanBox);
                }
                info.paragraf.forEach((p) => teks.appendChild(buatEl('p', '', p)));
                dalam.appendChild(teks);

                // Running Footer
                const footer = buatEl('div', 'halaman-footer');
                footer.appendChild(buatEl('span', 'footer-kiri', 'SOP Pengelolaan Berizin'));
                footer.appendChild(buatEl('span', 'footer-nomor', '— ' + nomor + ' —'));
                footer.appendChild(buatEl('span', 'footer-kanan', 'Hal. ' + nomor + ' / ' + totalHalaman));
                dalam.appendChild(footer);

                halaman.appendChild(dalam);
                return halaman;
            }

            function perbaruiStatus(idx) {
                if (!buku) return;
                const total = buku.getPageCount();
                const lanskap = buku.getOrientation() === 'landscape';
                let teks;
                if (idx <= 0) {
                    teks = 'Sampul depan';
                } else if (idx >= total - 1) {
                    teks = 'Sampul belakang';
                } else {
                    const halTampil = lanskap && idx < total - 1 ? idx + ' - ' + Math.min(idx + 1, total - 2) : idx;
                    teks = 'Halaman ' + halTampil + ' dari ' + (total - 2);
                }
                elStatus.textContent = teks;

                if (elProgress) {
                    const persen = total > 1 ? Math.min(Math.round((idx / (total - 1)) * 100), 100) : 0;
                    elProgress.style.width = persen + '%';
                }

                const nonaktifPrev = idx <= 0;
                const nonaktifNext = (idx + (lanskap ? 1 : 0)) >= total - 1;

                tombolSebelumnya.disabled = nonaktifPrev;
                tombolBerikutnya.disabled = nonaktifNext;
                if (tombolNavPrev) tombolNavPrev.disabled = nonaktifPrev;
                if (tombolNavNext) tombolNavNext.disabled = nonaktifNext;
            }

            function bersihkanBuku() {
                if (buku) {
                    try { buku.destroy(); } catch (err) { /* sudah dilepas */ }
                    buku = null;
                }
                areaBuku.innerHTML = '';
            }

            function tandaiKartu(idx) {
                document.querySelectorAll('[data-edukasi-idx]').forEach((k) => {
                    const aktif = idx >= 0 && parseInt(k.getAttribute('data-edukasi-idx'), 10) === idx;
                    k.setAttribute('aria-expanded', aktif ? 'true' : 'false');
                });
            }

            async function bukaBuku(idx, pemicuEl, dariUkuran) {
                const d = dataEdukasi[idx];
                if (!d) return;
                const token = ++tokenBuka;

                bersihkanBuku();
                pemicu = pemicuEl || pemicu;
                idxAktif = idx;
                elJudul.textContent = d.judul;
                elJenis.textContent = d.jenis;
                elStatus.textContent = 'Menyiapkan buku…';
                if (elProgress) elProgress.style.width = '0%';
                tombolSebelumnya.disabled = true;
                tombolBerikutnya.disabled = true;
                if (tombolNavPrev) tombolNavPrev.disabled = true;
                if (tombolNavNext) tombolNavNext.disabled = true;
                tandaiKartu(idx);
                ruang.classList.remove('hidden');

                if (!window.St || !window.St.PageFlip) {
                    areaBuku.appendChild(buatEl('p', 'text-sm text-[#D5E6DB] py-10', 'Pembaca buku belum termuat. Muat ulang halaman, lalu coba lagi.'));
                    elStatus.textContent = '';
                    return;
                }

                if (document.fonts && document.fonts.ready) {
                    try { await document.fonts.ready; } catch (err) { /* abaikan */ }
                }
                if (token !== tokenBuka) return;

                const u = ukuranHalaman();
                ukuranAktif = u;
                const info = bagiHalaman(d, u.w, u.h);
                const totalHalamanIsi = info.length;

                const halaman = [buatSampulDepan(d)];
                info.forEach((p, i) => halaman.push(buatHalamanIsi(p, i + 1, totalHalamanIsi, d)));
                halaman.push(buatSampulBelakang());

                const wadah = buatEl('div');
                wadah.style.width = (u.dua ? u.w * 2 : u.w) + 'px';
                wadah.style.height = u.h + 'px';
                wadah.style.margin = '0 auto';
                areaBuku.innerHTML = '';
                areaBuku.appendChild(wadah);

                buku = new window.St.PageFlip(wadah, {
                    width: u.w,
                    height: u.h,
                    size: 'fixed',
                    showCover: true,
                    usePortrait: !u.dua,
                    drawShadow: true,
                    maxShadowOpacity: 0.45,
                    flippingTime: kurangiGerak ? 50 : 600,
                    mobileScrollSupport: false,
                    swipeDistance: 25,
                    startPage: 0
                });

                buku.loadFromHTML(halaman);
                buku.on('flip', (e) => perbaruiStatus(e.data));
                buku.on('changeOrientation', () => perbaruiStatus(buku.getCurrentPageIndex()));
                perbaruiStatus(0);

                if (!dariUkuran) {
                    ruang.scrollIntoView({ behavior: kurangiGerak ? 'auto' : 'smooth', block: 'start' });
                    ruang.focus({ preventScroll: true });
                }
            }

            function tutupBuku(kembali) {
                tokenBuka++;
                bersihkanBuku();
                ruang.classList.add('hidden');
                idxAktif = -1;
                tandaiKartu(-1);
                if (kembali && pemicu) pemicu.focus();
            }

            document.querySelectorAll('[data-edukasi-idx]').forEach((el) => {
                el.addEventListener('click', () => {
                    const idx = parseInt(el.getAttribute('data-edukasi-idx'), 10);
                    if (idx === idxAktif && !ruang.classList.contains('hidden')) {
                        ruang.scrollIntoView({ behavior: kurangiGerak ? 'auto' : 'smooth', block: 'start' });
                        return;
                    }
                    bukaBuku(idx, el, false);
                });
            });

            tombolTutupBuku.addEventListener('click', () => tutupBuku(true));
            tombolSebelumnya.addEventListener('click', () => { if (buku) buku.flipPrev(); });
            tombolBerikutnya.addEventListener('click', () => { if (buku) buku.flipNext(); });
            if (tombolNavPrev) tombolNavPrev.addEventListener('click', () => { if (buku) buku.flipPrev(); });
            if (tombolNavNext) tombolNavNext.addEventListener('click', () => { if (buku) buku.flipNext(); });

            document.addEventListener('keydown', (e) => {
                if (ruang.classList.contains('hidden')) return;
                if (e.key === 'Escape') { tutupBuku(true); return; }
                if (!buku) return;
                const tag = (e.target && e.target.tagName) || '';
                if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
                if (e.key === 'ArrowRight') buku.flipNext();
                else if (e.key === 'ArrowLeft') buku.flipPrev();
            });

            // Susun ulang buku bila ukuran layar berubah (misalnya ponsel diputar atau resize window)
            window.addEventListener('resize', () => {
                if (ruang.classList.contains('hidden') || idxAktif < 0) return;
                clearTimeout(timerUkuran);
                timerUkuran = setTimeout(() => {
                    const u = ukuranHalaman();
                    if (!ukuranAktif || Math.abs(u.w - ukuranAktif.w) > 15 || Math.abs(u.h - ukuranAktif.h) > 15 || u.dua !== ukuranAktif.dua) {
                        bukaBuku(idxAktif, null, true);
                    }
                }, 300);
            });
        })();
    </script>
</body>
</html>