<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;
    
    protected $table = 'clientes';
    
    protected $fillable = [
        'nome',
        'email',
        'telefono',
        'traffic_source_id',
        'tipologia',
        'ragione_sociale',
        'codice_fiscale',
        'partita_iva',
        'prefisso_telefonico',
        'indirizzo_spedizione',
        'cap_spedizione',
        'citta_spedizione',
        'provincia_spedizione',
        'stato_spedizione',
        'indirizzo_fatturazione',
        'cap_fatturazione',
        'citta_fatturazione',
        'provincia_fatturazione',
        'stato_fatturazione',
        'fatturazione_uguale_spedizione'
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
    
    /**
     * Get the order requests associated with the client.
     */
    public function richiesteOrdine(): HasMany
    {
        return $this->hasMany(RichiestaOrdine::class);
    }
    
    /**
     * Calculate total amount spent by the client.
     */
    public function getTotaleSpesoAttribute(): float
    {
        // Se è disponibile il campo calcolato da withSum, usalo
        if (isset($this->attributes['ordini_sum_prezzo_vendita'])) {
            return (float) $this->attributes['ordini_sum_prezzo_vendita'];
        }
        
        // Altrimenti calcola manualmente
        return $this->ordini()->sum('prezzo_vendita') ?? 0;
    }
    
    /**
     * Get active subscriptions for the client.
     */
    public function getAbbonamentiAttiviAttribute()
    {
        $abbonamenti = collect();
        
        // Carica gli ordini con gli abbonamenti e i cast delle date pivot
        $this->load(['ordini.abbonamenti']);
        
        foreach ($this->ordini as $ordine) {
            foreach ($ordine->abbonamenti as $abbonamento) {
                if ($abbonamento->pivot->attivo && 
                    ($abbonamento->pivot->data_fine === null || $abbonamento->pivot->data_fine >= now())) {
                    $abbonamenti->push($abbonamento);
                }
            }
        }
        
        return $abbonamenti->unique('id');
    }
    
    /**
     * Get the earliest subscription expiry date.
     */
    public function getProssimaScadenzaAbbonamentiAttribute()
    {
        $abbonamenti = $this->getAbbonamentiAttiviAttribute();
        
        if ($abbonamenti->isEmpty()) {
            return null;
        }
        
        return $abbonamenti->min('pivot.data_fine');
    }
    
    /**
     * Check if client has active subscriptions.
     */
    public function getHaAbbonamentiAttiviAttribute(): bool
    {
        return $this->getAbbonamentiAttiviAttribute()->isNotEmpty();
    }
    
    /**
     * Get formatted list of active subscriptions with names and expiry dates.
     */
    public function getAbbonamentiAttiviFormattatiAttribute(): string
    {
        $abbonamenti = $this->getAbbonamentiAttiviAttribute();
        
        if ($abbonamenti->isEmpty()) {
            return 'Nessun abbonamento';
        }
        
        return $abbonamenti->map(function($abbonamento) {
            $dataFine = $abbonamento->pivot->data_fine;
            if ($dataFine) {
                // Se è una stringa, parsala con Carbon
                if (is_string($dataFine)) {
                    $dataFine = \Carbon\Carbon::parse($dataFine);
                }
                $scadenza = $dataFine->format('d/m/Y');
            } else {
                $scadenza = 'Senza scadenza';
            }
            return $abbonamento->nome . ' - ' . $scadenza;
        })->join(', ');
    }
}
