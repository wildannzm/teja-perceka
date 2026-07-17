<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nomor_bukti
 * @property Carbon $tanggal
 * @property string $keterangan
 * @property int $kode_akun_id
 * @property float $debet
 * @property float $kredit
 * @property int|null $transaksi_harian_id
 * @property int $unit_wisata_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class JurnalUmum extends Model
{
    use HasFactory;

    protected $table = 'jurnal_umum';

    protected $fillable = [
        'nomor_bukti',
        'tanggal',
        'keterangan',
        'kode_akun_id',
        'debet',
        'kredit',
        'transaksi_harian_id',
        'unit_wisata_id',
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
            'debet' => 'float',
            'kredit' => 'float',
        ];
    }

    /**
     * Get the account associated with the journal entry.
     *
     * @return BelongsTo<KodeAkun, $this>
     */
    public function kodeAkun(): BelongsTo
    {
        return $this->belongsTo(KodeAkun::class, 'kode_akun_id');
    }

    /**
     * Get the transaction associated with the journal entry.
     *
     * @return BelongsTo<TransaksiHarian, $this>
     */
    public function transaksiHarian(): BelongsTo
    {
        return $this->belongsTo(TransaksiHarian::class, 'transaksi_harian_id');
    }

    /**
     * Get the unit wisata associated with the journal entry.
     *
     * @return BelongsTo<UnitWisata, $this>
     */
    public function unitWisata(): BelongsTo
    {
        return $this->belongsTo(UnitWisata::class, 'unit_wisata_id');
    }
}
