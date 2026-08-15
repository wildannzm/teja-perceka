<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $unit_wisata_id
 * @property int $user_id
 * @property Carbon $tanggal
 * @property Carbon|null $tanggal_akhir
 * @property float $total_pemasukan
 * @property string|null $catatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class TransaksiHarian extends Model
{
    use HasFactory;

    protected $table = 'transaksi_harian';

    protected $fillable = [
        'unit_wisata_id',
        'user_id',
        'tanggal',
        'tanggal_akhir',
        'total_pemasukan',
        'total_pengeluaran',
        'catatan',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tanggal_akhir' => 'date',
            'total_pemasukan' => 'float',
            'total_pengeluaran' => 'float',
        ];
    }

    /**
     * Get the unit wisata associated with the transaction.
     *
     * @return BelongsTo<UnitWisata, $this>
     */
    public function unitWisata(): BelongsTo
    {
        return $this->belongsTo(UnitWisata::class, 'unit_wisata_id');
    }

    /**
     * Get the user who inputted the transaction.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the details of the transaction.
     *
     * @return HasMany<TransaksiDetail, $this>
     */
    public function detail(): HasMany
    {
        return $this->hasMany(TransaksiDetail::class, 'transaksi_harian_id');
    }
}
