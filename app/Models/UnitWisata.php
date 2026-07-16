<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nama
 * @property string $kode
 * @property string $frekuensi_input
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class UnitWisata extends Model
{
    use HasFactory;

    protected $table = 'unit_wisata';

    protected $fillable = [
        'nama',
        'kode',
        'frekuensi_input',
    ];

    /**
     * Get the users associated with the unit.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit_wisata_id');
    }

    /**
     * Get the transaction categories for the unit.
     *
     * @return HasMany<KategoriTransaksi, $this>
     */
    public function kategoriTransaksi(): HasMany
    {
        return $this->hasMany(KategoriTransaksi::class, 'unit_wisata_id');
    }
}
