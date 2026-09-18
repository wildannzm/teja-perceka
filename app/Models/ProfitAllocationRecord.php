<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfitAllocationRecord extends Model
{
    use HasFactory;

    protected $table = 'profit_allocation_history';

    protected $fillable = [
        'description',
        'percentage',
        'allocation_group',
        'effective_from',
        'business_unit_id',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'effective_from' => 'date',
    ];

    /**
     * Get the unit wisata associated with the allocation, if any.
     */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class, 'business_unit_id');
    }
}
