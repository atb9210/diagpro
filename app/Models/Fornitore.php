<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fornitore extends Model
{
    use HasFactory;
    
    protected $table = 'fornitoris';
    
    protected $fillable = [
        'nome',
        'descrizione',
        'logo',
        'link_sito',
        'email',
        'telefono',
        'indirizzo',
        'attivo'
    ];
    
    protected $casts = [
        'attivo' => 'boolean',
    ];
    
    /**
     * Get the products for the supplier.
     */
    public function prodotti(): HasMany
    {
        return $this->hasMany(Prodotto::class);
    }
    
    /**
     * Scope a query to only include active suppliers.
     */
    public function scopeAttivi($query)
    {
        return $query->where('attivo', true);
    }
}