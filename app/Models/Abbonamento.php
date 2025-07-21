<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Abbonamento extends Model
{
    use HasFactory;
    
    protected $table = 'abbonamentos';
    
    protected $fillable = [
        'nome',
        'descrizione',
        'prezzo',
        'costo',
        'durata',
        'frequenza_rinnovo',
        'attivo'
    ];
    
    protected $casts = [
        'prezzo' => 'decimal:2',
        'costo' => 'decimal:2',
        'attivo' => 'boolean',
    ];
    
    /**
     * Get the orders associated with the subscription.
     */
    public function ordini(): BelongsToMany
    {
        return $this->belongsToMany(Ordini::class, 'ordini_abbonamento', 'abbonamento_id', 'ordini_id')
                    ->withPivot('data_inizio', 'data_fine', 'prezzo', 'attivo')
                    ->withTimestamps();
    }
}
