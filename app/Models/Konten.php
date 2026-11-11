<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Konten extends Model
{
    protected $table = 'konten';
    protected $primaryKey = 'konten_id';

    protected $fillable = [
        'admin_id',
        'judul',
        'slug',
        'jenis',
        'ringkasan',
        'isi',
        'gambar_url',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Penanda batas halaman buku di dalam kolom `isi`.
     * Konten lama tanpa penanda tetap dibaca sebagai satu naskah utuh (dibagi otomatis di beranda).
     */
    public const PEMISAH_HALAMAN = '[[halaman-baru]]';

    /** Gabungkan daftar halaman dari form admin menjadi satu teks `isi`. */
    public static function gabungHalaman(array $halaman): string
    {
        $bersih = array_values(array_filter(
            array_map(fn ($h) => trim(str_replace(self::PEMISAH_HALAMAN, '', (string) $h)), $halaman),
            fn ($h) => $h !== ''
        ));

        return implode("\n".self::PEMISAH_HALAMAN."\n", $bersih);
    }

    /** Pecah `isi` menjadi daftar halaman (selalu minimal satu elemen). */
    public function daftarHalaman(): array
    {
        $bagian = array_values(array_filter(
            array_map('trim', explode(self::PEMISAH_HALAMAN, (string) $this->isi)),
            fn ($h) => $h !== ''
        ));

        return $bagian ?: [''];
    }

    /** Apakah admin sudah mengatur halaman secara manual. */
    public function punyaHalamanManual(): bool
    {
        return str_contains((string) $this->isi, self::PEMISAH_HALAMAN);
    }

    /** Teks polos tanpa penanda halaman, untuk cuplikan/ringkasan. */
    public function isiPolos(): string
    {
        return trim(preg_replace('/\s*'.preg_quote(self::PEMISAH_HALAMAN, '/').'\s*/', "\n\n", (string) $this->isi));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    public function scopeTerbit(Builder $query): Builder
    {
        return $query->where('status', 'terbit')->where('published_at', '<=', now());
    }
}

