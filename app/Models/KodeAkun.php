<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $kode
 * @property string $nama
 * @property string $tipe
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class KodeAkun extends Model
{
    use HasFactory;

    protected $table = 'kode_akun';

    protected $fillable = [
        'kode',
        'nama',
        'tipe',
        'urutan',
        'is_header',
    ];

    /**
     * Get the casts for the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'is_header' => 'boolean',
        ];
    }

    /**
     * Get the transaction categories associated with the account.
     *
     * @return HasMany<KategoriTransaksi, $this>
     */
    public function kategoriTransaksi(): HasMany
    {
        return $this->hasMany(KategoriTransaksi::class, 'kode_akun_id');
    }
}
