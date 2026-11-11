<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pos Kurir Angkut Medis & B3') · Kemedcar</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@600;700&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif

    <style>
        :root {
            --aspal: #1F2421;
            --beton: #F3F5F2;
            --hijau-kurir: #0F3B36;
            --hijau-kurir-aktif: #175E54;
            --kuning-rompi: #F2C230;
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
            outline: 2px solid #175E54;
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
        $userKurir = auth()->user();
        $profilKurir = $userKurir ? $userKurir->kurirProfil : null;

        $tugasPendingCount = \App\Models\Penjemputan::whereNull('kurir_id')
            ->where('status', 'menunggu')
            ->whereHas('setoran', fn ($q) => $q->where('status', 'menunggu_kurir'))
            ->count();

        $tugasAktifCount = $userKurir
            ? \App\Models\Penjemputan::where('kurir_id', $userKurir->user_id)
                ->whereIn('status', ['diterima', 'diangkut'])
                ->count()
            : 0;

        $menuKurir = [
            [
                'route' => 'kurir.dashboard',
                'label' => 'Manifes & Tugas Jemput',
                'badge' => $tugasAktifCount > 0 ? 'Aktif' : ($tugasPendingCount > 0 ? $tugasPendingCount : 0),
                'badge_style' => $tugasAktifCount > 0 ? 'bg-[#F2C230] text-[#1F2421]' : 'bg-emerald-500 text-white',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>',
            ],
            [
                'route' => 'kurir.riwayat',
                'label' => 'Riwayat Manifes Angkut',
                'badge' => 0,
                'badge_style' => '',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
            ],
            [
                'route' => 'kurir.profil',
                'label' => 'Profil & Armada B3',
                'badge' => 0,
                'badge_style' => '',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
            ],
        ];
    @endphp

    {{-- Sidebar Pos Kurir --}}
    <aside class="w-full lg:w-64 lg:min-h-dvh bg-[#0F3B36] text-white flex flex-col shrink-0 border-b lg:border-b-0 lg:border-r border-[#092522]">
        {{-- Header Brand Sidebar --}}
        <div class="px-5 py-4 flex items-center justify-between border-b border-white/10">
            <a href="{{ route('kurir.dashboard') }}" class="inline-flex items-center gap-2.5 font-judul font-bold text-base tracking-tight text-white">
                <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="h-7 w-auto max-w-[140px] shrink-0 rounded bg-white px-1.5 py-1 object-contain">
            </a>
            <span class="px-2 py-0.5 text-xs font-bold rounded bg-[#F2C230] text-[#1F2421]">KURIR</span>
        </div>

        {{-- Kartu Status Ketersediaan Kurir di Sidebar --}}
        @if ($profilKurir)
            @php
                $statusKetersediaan = $profilKurir->status_ketersediaan;
                $warnaStatus = match ($statusKetersediaan) {
                    'online' => 'bg-emerald-400',
                    'bertugas' => 'bg-amber-400',
                    default => 'bg-stone-400',
                };
                $labelStatus = match ($statusKetersediaan) {
                    'online' => 'Online · Siap Jemput',
                    'bertugas' => 'Sedang Bertugas',
                    default => 'Offline · Istirahat',
                };
            @endphp
            <div class="px-4 py-3 bg-black/20 border-b border-white/10 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-teal-200/80">Status Armada:</span>
                    <span class="inline-flex items-center gap-1.5 font-semibold text-white">
                        <span class="w-2 h-2 rounded-full {{ $warnaStatus }} {{ $statusKetersediaan === 'online' ? 'animate-pulse' : '' }}"></span>
                        <span class="uppercase text-[11px] tracking-wide">{{ $statusKetersediaan }}</span>
                    </span>
                </div>

                <div class="mt-1 text-white font-medium text-xs truncate">
                    {{ $labelStatus }}
                </div>

                <div class="mt-2.5 pt-2 border-t border-white/10 flex items-center justify-between text-[11px] text-teal-100/90 angka-tabular">
                    <span>Plat: <strong>{{ $profilKurir->plat_nomor ?: '-' }}</strong></span>
                    <span>Maks: <strong>{{ number_format($profilKurir->kapasitas_angkut_kg, 0, ',', '.') }} kg</strong></span>
                </div>

                {{-- Tombol Beralih Online / Offline --}}
                @if ($statusKetersediaan !== 'bertugas')
                    <form method="POST" action="{{ route('kurir.status') }}" class="mt-2.5">
                        @csrf
                        <input type="hidden" name="status" value="{{ $statusKetersediaan === 'online' ? 'offline' : 'online' }}">
                        <button type="submit"
                                class="w-full py-1.5 px-2.5 rounded text-xs font-semibold text-center transition cursor-pointer {{ $statusKetersediaan === 'online' ? 'bg-white/10 hover:bg-white/20 text-teal-100' : 'bg-emerald-600 hover:bg-emerald-500 text-white' }}">
                            {{ $statusKetersediaan === 'online' ? 'Istirahat (Offline)' : 'Nyalakan Mode Online' }}
                        </button>
                    </form>
                @endif
            </div>
        @endif

        {{-- Navigasi Menu (Susunan Atas ke Bawah pada Mobile & Desktop) --}}
        <nav aria-label="Menu Utama Kurir" class="p-3 flex flex-col gap-1">
            @foreach ($menuKurir as $item)
                @php $aktif = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="w-full flex items-center justify-between gap-2.5 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $aktif ? 'bg-[#175E54] text-white' : 'text-teal-100/85 hover:bg-white/10 hover:text-white' }}">
                    <span class="inline-flex items-center gap-2.5">
                        <svg class="w-5 h-5 shrink-0 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            {!! $item['icon'] !!}
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </span>
                    @if ($item['badge'] !== 0)
                        <span class="px-2 py-0.5 text-xs font-bold rounded {{ $item['badge_style'] }} angka-tabular">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        {{-- Informasi Akun Kurir di Bawah Sidebar --}}
        <div class="hidden lg:block mt-auto p-4 border-t border-white/10 text-xs text-teal-100/80">
            <div class="font-semibold text-white truncate">{{ $userKurir->nama }}</div>
            <div class="truncate mb-3 text-teal-200/70">{{ $userKurir->no_hp }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium text-xs text-center cursor-pointer transition-colors">
                    Keluar dari Pos Kurir
                </button>
            </form>
        </div>
    </aside>

    {{-- Konten Utama --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Topbar Header --}}
        <header class="bg-white border-b border-stone-200 px-4 sm:px-8 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#1F2421]">@yield('heading')</h1>
                @hasSection('subheading')
                    <p class="text-sm text-stone-600 mt-0.5">@yield('subheading')</p>
                @endif
            </div>

            <div class="flex items-center gap-3">
                @yield('header_actions')
                <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                    @csrf
                    <button type="submit" class="py-1.5 px-3 rounded-lg border border-stone-300 text-xs font-semibold text-stone-700 hover:bg-stone-100">
                        Keluar
                    </button>
                </form>
            </div>
        </header>

        {{-- Kontainer Konten Halaman --}}
        <main class="flex-1 px-4 sm:px-8 py-6 max-w-7xl w-full mx-auto space-y-6">
            @if (session('sukses'))
                <div role="status" class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-medium flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>{{ session('sukses') }}</span>
                    </div>
                </div>
            @endif

            @if (session('galat'))
                <div role="alert" class="p-3.5 rounded-lg bg-red-50 border border-red-300 text-red-900 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

</body>
</html>
