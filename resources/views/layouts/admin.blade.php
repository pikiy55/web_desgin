<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Regulator Admin') · Kemedcar</title>

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
            --hijau-tps: #133A26;
            --hijau-tong: #1D6B45;
            --kuning-rompi: #F2C230;
            --merah-b3: #B3261E;
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
        $jmlAkunPending = \App\Models\User::where('status', 'pending')->count();
        $jmlTpsPending = \App\Models\Tps::where('is_verified', false)->count();
        $menuList = [
            [
                'route' => 'admin.dashboard',
                'label' => 'Ikhtisar & Verifikasi Izin',
                'badge' => $jmlAkunPending + $jmlTpsPending,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
            ],
            [
                'route' => 'admin.akun',
                'label' => 'Akun & Verifikasi Peran',
                'badge' => $jmlAkunPending,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
            ],
            [
                'route' => 'admin.tps',
                'label' => 'Fasilitas & Izin Medis/B3',
                'badge' => $jmlTpsPending,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
            ],
            [
                'route' => 'admin.jenis-sampah',
                'label' => 'Klasifikasi Limbah & Poin',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
            ],
            [
                'route' => 'admin.konten',
                'label' => 'Protokol K3 & Edukasi',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>',
            ],
        ];
    @endphp

    {{-- Sidebar Navigasi --}}
    <aside class="w-full lg:w-64 lg:min-h-dvh bg-[#133A26] text-white flex flex-col shrink-0 border-b lg:border-b-0 lg:border-r border-[#0e2c1d]">
        <div class="px-5 py-4 flex items-center justify-between gap-2 border-b border-white/10">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2.5 font-judul font-bold text-base tracking-tight text-white min-w-0">
                <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="h-7 w-auto max-w-[140px] shrink-0 rounded bg-white px-1.5 py-1 object-contain">
            </a>
            <span class="px-2 py-0.5 text-xs font-bold rounded bg-[#F2C230] text-[#1F2421] shrink-0">ADMIN</span>
        </div>

        <nav aria-label="Menu Admin" class="p-3 flex flex-col gap-1">
            @foreach ($menuList as $item)
                @php $aktif = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}"
                   class="w-full flex items-center justify-between gap-2.5 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $aktif ? 'bg-[#1D6B45] text-white' : 'text-emerald-100/85 hover:bg-white/10 hover:text-white' }}">
                    <span class="inline-flex items-center gap-2.5">
                        <svg class="w-5 h-5 shrink-0 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            {!! $item['icon'] !!}
                        </svg>
                        <span>{{ $item['label'] }}</span>
                    </span>
                    @if ($item['badge'] > 0)
                        <span class="px-1.5 py-0.5 text-xs font-bold rounded bg-[#F2C230] text-[#1F2421] angka-tabular">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="hidden lg:block mt-auto p-4 border-t border-white/10 text-xs text-emerald-100/80">
            <div class="font-semibold text-white truncate">{{ auth()->user()->nama }}</div>
            <div class="truncate mb-3">{{ auth()->user()->email }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium text-xs text-center cursor-pointer transition-colors">
                    Keluar dari Akun
                </button>
            </form>
        </div>
    </aside>

    {{-- Area Konten Utama --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Baris Atas Mobile & Info Halaman --}}
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

        <main class="flex-1 px-4 sm:px-8 py-6 max-w-7xl w-full mx-auto space-y-6">
            @if (session('sukses'))
                <div role="status" class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-medium flex items-center justify-between">
                    <span>{{ session('sukses') }}</span>
                </div>
            @endif

            @if (session('galat'))
                <div role="alert" class="p-3.5 rounded-lg bg-red-50 border border-red-300 text-red-900 text-sm font-medium">
                    {{ session('galat') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="p-3.5 rounded-lg bg-red-50 border border-red-300 text-red-900 text-sm">
                    <p class="font-semibold mb-1">Periksa kembali isian berikut:</p>
                    <ul class="list-disc list-inside space-y-0.5">
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
