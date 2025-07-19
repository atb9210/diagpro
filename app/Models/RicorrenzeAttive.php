<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RicorrenzeAttive extends Model
{
    use HasFactory;
    
    protected $table = 'ordini_abbonamento';
    
    protected $fillable = [
        'ordini_id',
        'abbonamento_id',
        'data_inizio',
        'data_fine',
        'prezzo',
        'attivo',
        'costo',
        'note',
    ];
    
    protected $casts = [
        'data_inizio' => 'date',
        'data_fine' => 'date',
        'attivo' => 'boolean',
        'prezzo' => 'decimal:2',
        'costo' => 'decimal:2',
    ];
    
    /**
     * Scope per ottenere solo le ricorrenze attive
     */
    public function scopeAttive(Builder $query): Builder
    {
        return $query->where('attivo', true)
                    ->where(function ($q) {
                        $q->whereNull('data_fine')
                          ->orWhere('data_fine', '>=', now()->toDateString());
                    });
    }
    
    /**
     * Relazione con l'ordine
     */
    public function ordine(): BelongsTo
    {
        return $this->belongsTo(Ordini::class, 'ordini_id');
    }
    
    /**
     * Relazione con l'abbonamento
     */
    public function abbonamento(): BelongsTo
    {
        return $this->belongsTo(Abbonamento::class, 'abbonamento_id');
    }
    
    /**
     * Accessor per ottenere il nome del cliente
     */
    public function getClienteNomeAttribute(): ?string
    {
        return $this->ordine?->cliente?->nome;
    }
    
    /**
     * Accessor per verificare se l'abbonamento è scaduto
     */
    public function getIsScadutoAttribute(): bool
    {
        return $this->data_fine && $this->data_fine < now()->toDateString();
    }
    
    /**
     * Accessor per ottenere i giorni rimanenti
     */
    public function getGiorniRimanentiAttribute(): ?int
    {
        if (!$this->data_fine) {
            return null; // Abbonamento senza scadenza
        }
        
        return now()->diffInDays($this->data_fine, false);
    }
}
