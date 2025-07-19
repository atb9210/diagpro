<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;
    
    protected $table = 'categorias';
    
    protected $fillable = [
        'nome',
        'descrizione',
        'colore',
        'attiva'
    ];
    
    protected $casts = [
        'attiva' => 'boolean',
    ];
    
    /**
     * Get the products for the category.
     */
    public function prodotti(): HasMany
    {
        return $this->hasMany(Prodotto::class);
    }
    
    /**
     * Scope a query to only include active categories.
     */
    public function scopeAttive($query)
    {
        return $query->where('attiva', true);
    }
}