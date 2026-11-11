<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal Penghasil / Pengguna') · Kemedcar</title>

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
            --aspal: #1F2421;
            --beton: #F3F5F2;
            --hijau-utama: #133A26;
            --hijau-klinis: #1D6B45;
            --kuning-hazard: #F2C230;
            --merah-medis: #B3261E;
            --font-judul: 'Archivo', system-ui, sans-serif;
            --font-isi: 'Public Sans', system-ui, sans-serif;
        }
        body {
            font-family: var(--font-isi);
            background-color: var(--beton);
            color: var(--aspal);
        }
        h1, h2, h3, .font-judul {
            font-family: var(--font-judul);
        }
        .angka-tabular {
            font-variant-numeric: tabular-nums;
        }
        :focus-visible {
            outline: 2px solid #1D6B45;
            outline-offset: 2px;
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="min-h-dvh flex flex-col lg:flex-row bg-[#F3F5F2] text-[#1F2421] antialiased">

    @php
        $userAktif = auth()->user();
        $saldoPoinSidebar = $userAktif ? $userAktif->saldoPoin() : 0;
        $manifesBerjalanCount = $userAktif
            ? \App\Models\Setoran::where('pengguna_id', $userAktif->user_id)
                ->whereIn('status', ['menunggu_kurir', 'kurir_ditugaskan', 'diangkut', 'menuju_tps', 'tiba_di_tps'])
                ->count()
            : 0;

        $menuPengguna = [
            [
                'route' => 'pengguna.dashboard',
                'label' => 'Ikhtisar & Manifes Aktif',
                'badge' => $manifesBerjalanCount,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
            ],
            [
                'route' => 'pengguna.setoran.create',
                'label' => 'Buat Manifes Setoran',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4"/>',
            ],
            [
                'route' => 'pengguna.riwayat',
                'label' => 'Riwayat & Ulasan',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
            ],
            [
                'route' => 'pengguna.poin',
                'label' => 'Dompet & Tukar Poin',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
            [
                'route' => 'game.pilah',
                'label' => 'Game Misi Pilah',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h8a4 4 0 014 4v5a2 2 0 01-3.414 1.414L14 15H10l-2.586 2.414A2 2 0 014 16v-5a4 4 0 014-4z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10v4m-2-2h4m6-1h.01M18 13h.01"/>',
            ],
            [
                'route' => 'pengguna.alamat',
                'label' => 'Alamat Jemput & Profil',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
            ],
        ];
    @endphp

    {{-- Sidebar Portal Penghasil / Pengguna (Susunan Atas ke Bawah pada Mobile & Desktop) --}}
    <aside class="w-full lg:w-64 lg:min-h-dvh bg-[#133A26] text-white flex flex-col shrink-0 border-b lg:border-b-0 lg:border-r border-[#0e2c1d]">
        <div class="px-5 py-4 flex items-center justify-between gap-2 border-b border-white/10">
            <a href="{{ route('pengguna.dashboard') }}" class="inline-flex items-center gap-2.5 font-judul font-bold text-base tracking-tight text-white min-w-0">
                <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="h-7 w-auto max-w-[140px] shrink-0 rounded bg-white px-1.5 py-1 object-contain">
            </a>

            <div class="flex items-center gap-2 shrink-0">
                <span class="px-2 py-0.5 text-xs font-bold rounded bg-[#F2C230] text-[#1F2421]">WARGA</span>
                {{-- Tombol Buka/Tutup Menu di Mobile (Tetap tersusun dari atas ke bawah) --}}
                <button type="button"
                        onclick="toggleMenuMobilePengguna()"
                        aria-expanded="true"
                        aria-controls="panel-nav-pengguna"
                        aria-label="Buka atau tutup daftar menu navigasi"
                        class="lg:hidden inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-white cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span id="label-toggle-menu">Menu</span>
                </button>
            </div>
        </div>

        {{-- Kartu Ringkas Saldo Poin di Sidebar --}}
        <div class="px-4 py-3 bg-black/15 border-b border-white/10 text-xs flex items-center justify-between">
            <div>
                <span class="text-emerald-200/80 block">Saldo Poin Insentif B3:</span>
                <div class="mt-0.5 flex items-baseline gap-1.5 angka-tabular">
                    <span class="text-lg font-extrabold text-white">{{ number_format($saldoPoinSidebar, 0, ',', '.') }}</span>
                    <span class="text-emerald-200/80">poin aktif</span>
                </div>
            </div>
            <a href="{{ route('pengguna.poin') }}" class="px-2.5 py-1.5 rounded-lg bg-[#1D6B45] hover:bg-[#155235] text-[#F2C230] font-semibold text-xs">
                Tukar Poin →
            </a>
        </div>

        {{-- Daftar Navigasi Vertikal (Dari Atas ke Bawah, Tanpa Geser Kanan) --}}
        <div id="panel-nav-pengguna" class="flex flex-col flex-1">
            <nav aria-label="Menu Utama Pengguna" class="p-3 flex flex-col gap-1">
                @foreach ($menuPengguna as $item)
                    @php $aktif = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="w-full flex items-center justify-between gap-2.5 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $aktif ? 'bg-[#1D6B45] text-white shadow-2xs' : 'text-emerald-100/85 hover:bg-white/10 hover:text-white' }}">
                        <span class="inline-flex items-center gap-2.5">
                            <svg class="w-5 h-5 shrink-0 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                {!! $item['icon'] !!}
                            </svg>
                            <span>{{ $item['label'] }}</span>
                        </span>
                        @if ($item['badge'] > 0)
                            <span class="px-2 py-0.5 text-xs font-bold rounded bg-[#F2C230] text-[#1F2421] angka-tabular">
                                {{ $item['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto p-4 border-t border-white/10 text-xs text-emerald-100/80 space-y-3">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <div class="font-semibold text-white truncate">{{ $userAktif->nama }}</div>
                        <div class="truncate text-emerald-200/75 angka-tabular">{{ $userAktif->no_hp }}</div>
                    </div>
                    <a href="{{ url('/') }}" class="px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-emerald-100 font-medium text-xs shrink-0 transition-colors">
                        Beranda
                    </a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium text-xs text-center cursor-pointer transition-colors">
                        Keluar dari Akun
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Konten Utama --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-stone-200 px-4 sm:px-8 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#1F2421]">@yield('heading')</h1>
                @hasSection('subheading')
                    <p class="text-sm text-stone-600 mt-0.5">@yield('subheading')</p>
                @endif
            </div>

            <div class="flex items-center gap-2.5">
                @yield('header_actions')
            </div>
        </header>

        <main class="flex-1 px-4 sm:px-8 py-6 max-w-7xl w-full mx-auto space-y-6">
            @if (session('sukses'))
                <div role="status" class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('sukses') }}</span>
                </div>
            @endif

            @if (session('galat'))
                <div role="alert" class="p-3.5 rounded-lg bg-red-50 border border-red-300 text-red-900 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>{{ session('galat') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="p-3.5 rounded-lg bg-red-50 border border-red-300 text-red-900 text-sm">
                    <p class="font-semibold mb-1">Periksa kembali isian formulir:</p>
                    <ul class="list-disc list-inside space-y-0.5 text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleMenuMobilePengguna() {
            const panel = document.getElementById('panel-nav-pengguna');
            if (!panel) return;
            panel.classList.toggle('hidden');
        }
    </script>
</body>
</html>
