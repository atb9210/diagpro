<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ordini extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'cliente_id',
        'traffic_source_id',
        'data',
        'tipo_vendita',
        'link_ordine',
        'prezzo_vendita',
        'costo_marketing',
        'costo_prodotto',
        'costo_spedizione',
        'altri_costi',
        'margine',
        'vat',
        'note'
    ];
    
    protected $casts = [
        'data' => 'date',
        'vat' => 'boolean',
        'prezzo_vendita' => 'decimal:2',
        'costo_marketing' => 'decimal:2',
        'costo_prodotto' => 'decimal:2',
        'costo_spedizione' => 'decimal:2',
        'altri_costi' => 'decimal:2',
        'margine' => 'decimal:2',
    ];
    
    /**
     * Get the cliente associated with the order.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
    
    /**
     * Get the traffic source associated with the order.
     */
    public function trafficSource(): BelongsTo
    {
        return $this->belongsTo(TrafficSource::class);
    }
    
    /**
     * Get the products associated with the order.
     */
    public function prodotti(): BelongsToMany
    {
        return $this->belongsToMany(Prodotto::class, 'ordini_prodotto', 'ordini_id', 'prodotto_id')
                    ->withPivot('quantita', 'prezzo_unitario', 'costo')
                    ->withTimestamps();
    }
    
    /**
     * Get the subscriptions associated with the order.
     */
    public function abbonamenti(): BelongsToMany
    {
        return $this->belongsToMany(Abbonamento::class, 'ordini_abbonamento', 'ordini_id', 'abbonamento_id')
                    ->withPivot('data_inizio', 'data_fine', 'prezzo', 'attivo', 'costo')
                    ->withTimestamps();
    }
    
    /**
     * Get the shipping associated with the order.
     */
    public function spedizione(): HasOne
    {
        return $this->hasOne(Spedizione::class, 'ordini_id');
    }
    
    /**
     * Calculate the margin based on the sale price and costs.
     */
    public function calculateAndSaveCostoProdotto()
    {
        $totalCost = 0;
        // We need to refresh the relations in case they were just added
        $this->load('prodotti', 'abbonamenti');

        if ($this->prodotti) {
            foreach ($this->prodotti as $prodotto) {
                $totalCost += $prodotto->pivot->costo * ($prodotto->pivot->quantita ?? 1);
            }
        }

        if ($this->abbonamenti) {
            foreach ($this->abbonamenti as $abbonamento) {
                $totalCost += $abbonamento->pivot->costo;
            }
        }

        $this->costo_prodotto = $totalCost;
        $this->saveQuietly(); // Use saveQuietly to avoid triggering events again
    }

    public function calcolaMargine()
    {
        $totaleCosti = $this->costo_marketing + $this->costo_prodotto + 
                       $this->costo_spedizione + $this->altri_costi;
        $this->margine = $this->prezzo_vendita - $totaleCosti;
        return $this->margine;
    }
}