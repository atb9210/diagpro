<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Shop extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'nome',
        'slug',
        'descrizione',
        'logo',
        'attivo',
        'meta_title',
        'meta_description',
        'colore_primario',
        'colore_secondario'
    ];
    
    protected $casts = [
        'attivo' => 'boolean',
    ];
    
    /**
     * Get the products associated with the shop.
     */
    public function prodotti(): BelongsToMany
    {
        return $this->belongsToMany(Prodotto::class, 'prodotto_shop')
                    ->withPivot('prezzo_personalizzato', 'attivo', 'ordine', 'configurazione')
                    ->withTimestamps();
    }
    
    /**
     * Get the orders for the shop.
     */
    public function ordini(): HasMany
    {
        return $this->hasMany(Ordini::class);
    }
    
    /**
     * Get the order requests for the shop.
     */
    public function richiesteOrdine(): HasMany
    {
        return $this->hasMany(RichiestaOrdine::class);
    }
    
    /**
     * Get only active products for the shop.
     */
    public function prodottiAttivi(): BelongsToMany
    {
        return $this->prodotti()->wherePivot('attivo', true);
    }
    
    /**
     * Get products ordered by their display order in the shop.
     */
    public function prodottiOrdinati(): BelongsToMany
    {
        return $this->prodottiAttivi()->orderByPivot('ordine', 'asc');
    }
    
    /**
     * Scope a query to only include active shops.
     */
    public function scopeAttivi($query)
    {
        return $query->where('attivo', true);
    }
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($shop) {
            if (empty($shop->slug)) {
                $shop->slug = Str::slug($shop->nome);
            }
        });
        
        static::updating(function ($shop) {
            if ($shop->isDirty('nome') && empty($shop->slug)) {
                $shop->slug = Str::slug($shop->nome);
            }
        });
    }
    
    /**
     * Get the public URL for the shop.
     */
    public function getPublicUrlAttribute()
    {
        return url('/shop/' . $this->slug);
    }
}