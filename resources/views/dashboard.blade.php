@extends('layouts.pengguna')

@section('title', 'Ikhtisar & Manifes Aktif')
@section('heading', 'Halo, '.(auth()->user()->nama ?? 'Pengguna'))
@section('subheading', 'Kelola penyetoran limbah medis, B3, dan daur ulang Anda ke fasilitas penampungan berizin.')

@section('header_actions')
    <a href="{{ route('pengguna.setoran.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs sm:text-sm font-semibold shadow-2xs transition cursor-pointer">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Buat Manifes Setoran Baru</span>
    </a>
@endsection

@section('content')
    <div class="bg-white border border-stone-200 rounded-xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="flex flex-wrap items-center gap-x-2 font-judul font-bold text-base text-[#1F2421]">
                <span>Portal</span>
                <img src="{{ asset('images/logo-kemadwast.png') }}" alt="KemaDwast" class="w-24 h-auto">
                <span>· Limbah Medis &amp; B3</span>
            </h2>
            <p class="text-xs sm:text-sm text-stone-600 mt-1">
                Gunakan menu navigasi di samping (atau di atas pada tampilan HP) untuk membuat manifes setoran, melihat riwayat timbangan, menukar poin K3, atau mengatur titik jemput.
            </p>
        </div>
        <a href="{{ route('pengguna.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs sm:text-sm font-semibold shrink-0">
            Buka Ikhtisar Lengkap →
        </a>
    </div>
@endsection
