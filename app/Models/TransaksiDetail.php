<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $transaksi_harian_id
 * @property int $kategori_transaksi_id
 * @property int|null $qty
 * @property float $harga_satuan
 * @property float $subtotal
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TransaksiDetail extends Model
{
    use HasFactory;

    protected $table = 'transaksi_detail';

    protected $fillable = [
        'transaksi_harian_id',
        'kategori_transaksi_id',
        'qty',
        'harga_satuan',
        'subtotal',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'harga_satuan' => 'float',
            'subtotal' => 'float',
        ];
    }

    /**
     * Get the transaction header associated with this detail.
     *
     * @return BelongsTo<TransaksiHarian, $this>
     */
    public function transaksiHarian(): BelongsTo
    {
        return $this->belongsTo(TransaksiHarian::class, 'transaksi_harian_id');
    }

    /**
     * Get the category associated with this detail.
     *
     * @return BelongsTo<KategoriTransaksi, $this>
     */
    public function kategoriTransaksi(): BelongsTo
    {
        return $this->belongsTo(KategoriTransaksi::class, 'kategori_transaksi_id');
    }
}
