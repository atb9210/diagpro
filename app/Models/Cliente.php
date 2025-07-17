<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nome',
        'email',
        'telefono',
        'indirizzo',
        'traffic_source_id',
    ];
    
    /**
     * Get the traffic source that the client came from.
     */
    public function trafficSource(): BelongsTo
    {
        return $this->belongsTo(TrafficSource::class);
    }
    
    /**
     * Get the orders associated with the client.
     */
    public function ordini(): HasMany
    {
        return $this->hasMany(Ordini::class);
    }
}
