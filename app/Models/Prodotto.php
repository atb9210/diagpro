<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prodotto extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nome',
        'descrizione',
        'prezzo',
        'costo',
        'tipo',
        'quantita_disponibile'
    ];
    
    protected $casts = [
        'prezzo' => 'decimal:2',
        'costo' => 'decimal:2',
    ];
    
    /**
     * Get the orders associated with the product.
     */
    public function ordini(): BelongsToMany
    {
        return $this->belongsToMany(Ordini::class, 'ordini_prodotto', 'prodotto_id', 'ordini_id')
                    ->withPivot('quantita', 'prezzo_unitario')
                    ->withTimestamps();
    }
}
