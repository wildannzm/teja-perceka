<?php

namespace App\Models;

use App\Enums\JenisTransaksi;
use App\Enums\TipeKategori;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $unit_wisata_id
 * @property int|null $kode_akun_id
 * @property string $nama
 * @property TipeKategori $tipe
 * @property JenisTransaksi $jenis
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class KategoriTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kategori_transaksi';

    protected $fillable = [
        'unit_wisata_id',
        'kode_akun_id',
        'nama',
        'tipe',
        'jenis',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipe' => TipeKategori::class,
            'jenis' => JenisTransaksi::class,
        ];
    }

    /**
     * Get the unit wisata associated with the category.
     *
     * @return BelongsTo<UnitWisata, $this>
     */
    public function unitWisata(): BelongsTo
    {
        return $this->belongsTo(UnitWisata::class, 'unit_wisata_id');
    }

    /**
     * Get the account code associated with the category.
     *
     * @return BelongsTo<KodeAkun, $this>
     */
    public function kodeAkun(): BelongsTo
    {
        return $this->belongsTo(KodeAkun::class, 'kode_akun_id');
    }

    /**
     * Get the price history associated with the category.
     *
     * @return HasMany<KategoriHargaRiwayat, $this>
     */
    public function hargaRiwayat(): HasMany
    {
        return $this->hasMany(KategoriHargaRiwayat::class, 'kategori_transaksi_id');
    }

    /**
     * Get the price applicable for a specific date.
     */
    public function hargaSaat(string|\DateTimeInterface $date): float
    {
        $riwayat = $this->hargaRiwayat()
            ->where('berlaku_dari', '<=', $date)
            ->orderBy('berlaku_dari', 'desc')
            ->first();

        return $riwayat ? (float) $riwayat->harga : 0.0;
    }
}
