<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlokasiLabaRiwayat extends Model
{
    use HasFactory;

    protected $table = 'alokasi_laba_riwayat';

    protected $fillable = [
        'keterangan',
        'persentase',
        'kelompok',
        'berlaku_dari',
        'unit_wisata_id',
    ];

    protected $casts = [
        'persentase' => 'decimal:2',
        'berlaku_dari' => 'date',
    ];

    /**
     * Get the unit wisata associated with the allocation, if any.
     */
    public function unitWisata(): BelongsTo
    {
        return $this->belongsTo(UnitWisata::class, 'unit_wisata_id');
    }
}
