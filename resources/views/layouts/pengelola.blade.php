<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pos Fasilitas Medis & B3') · Kemedcar</title>

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
            --hijau-tps: #16442D;
            --hijau-tong: #1D6B45;
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
        $antreanTimbang = isset($tps) && $tps
            ? \App\Models\Setoran::where('tps_id', $tps->tps_id)
                ->whereIn('status', ['tiba_di_tps', 'menuju_tps', 'diangkut'])
                ->count()
            : 0;

        $menuPengelola = [
            [
                'route' => 'pengelola.dashboard',
                'label' => 'Meja Timbang & Manifes',
                'badge' => $antreanTimbang,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>',
            ],
            [
                'route' => 'pengelola.kapasitas',
                'label' => 'Kuota Simpan Medis & B3',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
            ],
            [
                'route' => 'pengelola.riwayat',
                'label' => 'Riwayat Manifes & Ulasan',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
            ],
            [
                'route' => 'pengelola.profil-tps',
                'label' => 'Izin & Profil Fasilitas',
                'badge' => 0,
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
            ],
        ];
    @endphp

    {{-- Sidebar Pos Pengelola TPS --}}
    <aside class="w-full lg:w-64 lg:min-h-dvh bg-[#16442D] text-white flex flex-col shrink-0 border-b lg:border-b-0 lg:border-r border-[#0f3120]">
        <div class="px-5 py-4 flex items-center justify-between border-b border-white/10">
            <a href="{{ route('pengelola.dashboard') }}" class="inline-flex items-center gap-2.5 font-judul font-bold text-base tracking-tight text-white">
                <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="h-7 w-auto max-w-[140px] shrink-0 rounded bg-white px-1.5 py-1 object-contain">
            </a>
            <span class="px-2 py-0.5 text-xs font-bold rounded bg-[#F2C230] text-[#1F2421]">POS</span>
        </div>

        {{-- Info 1 TPS Milik Pengelola --}}
        @if (isset($tps) && $tps)
            <div class="px-4 py-3 bg-black/15 border-b border-white/10 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-emerald-200/80">Fasilitas Anda:</span>
                    @if ($tps->izin_limbah_medis)
                        <span class="px-1.5 py-0.5 rounded bg-red-600 text-white font-bold text-[10px] uppercase">Izin Medis</span>
                    @endif
                </div>
                <div class="font-bold text-white text-sm truncate mt-0.5">{{ $tps->nama_tps }}</div>

                <div class="mt-2 flex items-center justify-between angka-tabular">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $tps->status === 'buka' ? 'bg-emerald-400' : ($tps->status === 'penuh' ? 'bg-red-400' : 'bg-stone-400') }}"></span>
                        <span class="uppercase font-bold text-white">{{ $tps->status }}</span>
                    </span>
                    <span class="text-emerald-100/90">
                        {{ number_format($tps->kapasitas_terpakai_kg, 0, ',', '.') }}/{{ number_format($tps->kapasitas_total_kg, 0, ',', '.') }} kg
                    </span>
                </div>
            </div>
        @endif

        <nav aria-label="Menu Pengelola TPS" class="p-3 flex flex-col gap-1">
            @foreach ($menuPengelola as $item)
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
            <div class="truncate mb-3">{{ auth()->user()->no_hp }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2 px-3 rounded-lg bg-white/10 hover:bg-white/20 text-white font-medium text-xs text-center cursor-pointer transition-colors">
                    Keluar dari Pos TPS
                </button>
            </form>
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
                <div role="status" class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-300 text-emerald-900 text-sm font-medium">
                    {{ session('sukses') }}
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
