@extends('layouts.admin')

@section('title', 'Akun & Verifikasi Peran')
@section('heading', 'Akun Penghasil, Fasilitas Medis/B3 & Kurir')
@section('subheading', 'Kelola dan verifikasi akun penghasil limbah, pengelola fasilitas berizin (1 akun = 1 fasilitas), kurir B3, dan admin.')

@section('content')

    {{-- Filter & Pencarian --}}
    <section class="bg-white border border-stone-200 rounded-xl p-4">
        <form method="GET" action="{{ route('admin.akun') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="cari" class="block text-xs font-semibold text-stone-700 mb-1">Cari Nama / Email / No HP</label>
                <input id="cari" type="text" name="cari" value="{{ request('cari') }}"
                       placeholder="Ketik nama, email, atau 0812…"
                       class="w-full px-3 py-2 rounded-lg border border-stone-300 text-sm bg-white">
            </div>

            <div>
                <label for="role" class="block text-xs font-semibold text-stone-700 mb-1">Peran (Role)</label>
                <select id="role" name="role" class="px-3 py-2 rounded-lg border border-stone-300 text-sm bg-white">
                    <option value="">Semua Peran</option>
                    <option value="pengguna" @selected(request('role') === 'pengguna')>Pengguna</option>
                    <option value="pengelola_tps" @selected(request('role') === 'pengelola_tps')>Pengelola TPS</option>
                    <option value="kurir" @selected(request('role') === 'kurir')>Kurir</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                </select>
            </div>

            <div>
                <label for="status" class="block text-xs font-semibold text-stone-700 mb-1">Status Akun</label>
                <select id="status" name="status" class="px-3 py-2 rounded-lg border border-stone-300 text-sm bg-white">
                    <option value="">Semua ({{ $hitungPerStatus['semua'] }})</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending ({{ $hitungPerStatus['pending'] }})</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif ({{ $hitungPerStatus['aktif'] }})</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif ({{ $hitungPerStatus['nonaktif'] }})</option>
                    <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak ({{ $hitungPerStatus['ditolak'] }})</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-lg bg-[#1D6B45] hover:bg-[#13502F] text-white text-sm font-semibold cursor-pointer">
                    Terapkan
                </button>
                @if (request()->hasAny(['cari', 'role', 'status']))
                    <a href="{{ route('admin.akun') }}" class="px-3 py-2 rounded-lg border border-stone-300 text-stone-700 hover:bg-stone-100 text-sm font-medium">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </section>

    {{-- Tabel Daftar Akun --}}
    <section class="bg-white border border-stone-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-stone-200 bg-stone-50 text-xs font-semibold text-stone-600">
                        <th class="py-3 px-4">Nama & Kontak</th>
                        <th class="py-3 px-4">Peran</th>
                        <th class="py-3 px-4">Detail Peran (TPS / Kendaraan)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Login Terakhir</th>
                        <th class="py-3 px-4 text-right">Tindakan Admin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse ($daftarAkun as $u)
                        <tr class="hover:bg-stone-50/70">
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#1F2421]">{{ $u->nama }}</div>
                                <div class="text-xs text-stone-500 angka-tabular">{{ $u->email }} · {{ $u->no_hp }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $labelRole = match ($u->role) {
                                        'admin' => ['Admin', 'bg-stone-800 text-white'],
                                        'pengelola_tps' => ['Pengelola TPS', 'bg-amber-100 text-amber-900'],
                                        'kurir' => ['Kurir', 'bg-sky-100 text-sky-900'],
                                        default => ['Pengguna', 'bg-emerald-100 text-emerald-900'],
                                    };
                                @endphp
                                <span class="px-2.5 py-0.5 text-xs font-semibold rounded {{ $labelRole[1] }}">
                                    {{ $labelRole[0] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-stone-600">
                                @if ($u->role === 'kurir' && $u->kurirProfil)
                                    <div class="font-medium text-stone-800">
                                        {{ $u->kurirProfil->jenis_kendaraan }} · {{ $u->kurirProfil->plat_nomor }}
                                    </div>
                                    <div class="text-stone-500 angka-tabular">
                                        Kapasitas: {{ $u->kurirProfil->kapasitas_angkut_kg ? number_format($u->kurirProfil->kapasitas_angkut_kg, 0, ',', '.').' kg' : '-' }}
                                        · Status: {{ $u->kurirProfil->status_ketersediaan }}
                                    </div>
                                @elseif ($u->role === 'pengelola_tps')
                                    @if ($u->tpsDikelola)
                                        <div class="font-medium text-stone-800">
                                            {{ $u->tpsDikelola->nama_tps }}
                                        </div>
                                    @else
                                        <span class="text-stone-400">Belum mendaftarkan TPS</span>
                                    @endif
                                @else
                                    <span class="text-stone-400">—</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @php
                                    $badgeStatus = match ($u->status) {
                                        'aktif' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                                        'pending' => 'bg-amber-50 text-amber-800 border-amber-300',
                                        'ditolak' => 'bg-red-50 text-red-800 border-red-300',
                                        default => 'bg-stone-100 text-stone-700 border-stone-300',
                                    };
                                @endphp
                                <span class="inline-block px-2.5 py-0.5 text-xs font-bold uppercase rounded border {{ $badgeStatus }}">
                                    {{ $u->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-xs text-stone-500 angka-tabular">
                                {{ $u->last_login_at ? $u->last_login_at->format('d/m/Y H:i') : 'Belum pernah' }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if ($u->user_id === auth()->id())
                                    <span class="text-xs text-stone-400">Akun Anda</span>
                                @else
                                    <div class="inline-flex items-center justify-end gap-1.5 flex-wrap">
                                        @if ($u->status === 'pending')
                                            <form method="POST" action="{{ route('admin.akun.status', $u) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="aktif">
                                                <button type="submit" class="px-2.5 py-1 rounded bg-[#1D6B45] hover:bg-[#13502F] text-white text-xs font-semibold cursor-pointer">
                                                    Verifikasi
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.akun.status', $u) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="ditolak">
                                                <button type="submit" class="px-2.5 py-1 rounded border border-red-300 text-red-700 hover:bg-red-50 text-xs font-semibold cursor-pointer">
                                                    Tolak
                                                </button>
                                            </form>
                                        @elseif ($u->status === 'aktif')
                                            <form method="POST" action="{{ route('admin.akun.status', $u) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="nonaktif">
                                                <button type="submit" class="px-2.5 py-1 rounded border border-stone-300 text-stone-700 hover:bg-stone-100 text-xs font-semibold cursor-pointer">
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.akun.status', $u) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="aktif">
                                                <button type="submit" class="px-2.5 py-1 rounded border border-emerald-400 text-emerald-800 hover:bg-emerald-50 text-xs font-semibold cursor-pointer">
                                                    Aktifkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-sm text-stone-500">
                                Tidak ada akun yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($daftarAkun->hasPages())
            <div class="px-4 py-3 border-t border-stone-200">
                {{ $daftarAkun->links() }}
            </div>
        @endif
    </section>

@endsection

