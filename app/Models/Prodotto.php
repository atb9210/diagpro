<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prodotto extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nome',
        'sku',
        'descrizione',
        'prezzo',
        'costo',
        'tipo',
        'quantita_disponibile',
        'peso',
        'lunghezza',
        'larghezza',
        'altezza',
        'categoria_id',
        'fornitore_id',
        'immagini',
        'immagine_copertina',
        'stato',
        'data_arrivo'
    ];
    
    protected $casts = [
        'prezzo' => 'decimal:2',
        'costo' => 'decimal:2',
        'peso' => 'decimal:3',
        'lunghezza' => 'decimal:2',
        'larghezza' => 'decimal:2',
        'altezza' => 'decimal:2',
        'immagini' => 'array',
        'data_arrivo' => 'date',
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
    
    /**
     * Get the category that owns the product.
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
    
    /**
     * Get the supplier that owns the product.
     */
    public function fornitore(): BelongsTo
    {
        return $this->belongsTo(Fornitore::class);
    }
    
    /**
     * Scope a query to only include active products.
     */
    public function scopeAttivi($query)
    {
        return $query->where('stato', 'attivo');
    }
    
    /**
     * Scope a query to only include discontinued products.
     */
    public function scopeDiscontinui($query)
    {
        return $query->where('stato', 'discontinuo');
    }
    
    /**
     * Scope a query to only include out of stock products.
     */
    public function scopeEsauriti($query)
    {
        return $query->where('stato', 'esaurito');
    }
}
