<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Spedizione extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'ordini_id',
        'corriere',
        'numero_tracciamento',
        'stato',
        'data_spedizione',
        'data_consegna_prevista',
        'data_consegna_effettiva',
        'indirizzo_spedizione',
        'note'
    ];
    
    protected $casts = [
        'data_spedizione' => 'date',
        'data_consegna_prevista' => 'date',
        'data_consegna_effettiva' => 'date',
    ];
    
    /**
     * Get the order associated with the shipping.
     */
    public function ordine(): BelongsTo
    {
        return $this->belongsTo(Ordini::class, 'ordini_id');
    }
}