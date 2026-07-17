<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $kategori_transaksi_id
 * @property float $harga
 * @property Carbon $berlaku_dari
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class KategoriHargaRiwayat extends Model
{
    use HasFactory;

    protected $table = 'kategori_harga_riwayat';

    protected $fillable = [
        'kategori_transaksi_id',
        'harga',
        'berlaku_dari',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga' => 'float',
            'berlaku_dari' => 'date',
        ];
    }

    /**
     * Get the category associated with this price history.
     *
     * @return BelongsTo<KategoriTransaksi, $this>
     */
    public function kategoriTransaksi(): BelongsTo
    {
        return $this->belongsTo(KategoriTransaksi::class, 'kategori_transaksi_id');
    }
}
