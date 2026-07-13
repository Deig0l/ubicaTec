<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationSynonym extends Model
{
    use HasFactory;

    /**
     * La tabla location_synonyms no tiene columnas created_at/updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'location_id',
        'name',
    ];

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
