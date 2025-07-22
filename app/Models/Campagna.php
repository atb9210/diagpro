<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campagna extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'data_inizio',
        'nome_campagna',
        'stato',
        'traffic_source_id',
        'budget',
        'spesa',
        'budget_type',
        'data_fine',
        'note',
    ];

    protected $casts = [
        'data_inizio' => 'date',
        'data_fine' => 'date',
        'budget' => 'decimal:2',
        'spesa' => 'decimal:2',
    ];

    public const STATI = [
        'attiva' => 'Attiva',
        'pausa' => 'Pausa',
        'terminata' => 'Terminata',
        'in_review' => 'In Review',
    ];

    public const BUDGET_TYPES = [
        'giornaliero' => 'Giornaliero',
        'settimanale' => 'Settimanale',
        'mensile' => 'Mensile',
        'annuale' => 'Annuale',
    ];

    /**
     * Relazione con TrafficSource
     */
    public function trafficSource(): BelongsTo
    {
        return $this->belongsTo(TrafficSource::class);
    }

    /**
     * Relazione con gli ordini associati
     */
    public function ordini(): HasMany
    {
        return $this->hasMany(Ordini::class);
    }

    /**
     * Calcola il numero di vendite (ordini associati)
     */
    public function getVenditeAttribute(): int
    {
        return $this->ordini()->count();
    }

    /**
     * Calcola il ROI della campagna
     */
    public function getRoiAttribute(): float
    {
        if ($this->spesa == 0) {
            return 0;
        }

        $ricavi = $this->ordini()->sum('prezzo_vendita');
        return (($ricavi - $this->spesa) / $this->spesa) * 100;
    }

    /**
     * Calcola il costo per acquisizione
     */
    public function getCostoPerAcquisizioneAttribute(): float
    {
        $vendite = $this->getVenditeAttribute();
        return $vendite > 0 ? $this->spesa / $vendite : 0;
    }

    public function getGiorniRimanentiAttribute(): ?int
    {
        if (!$this->data_fine) {
            return null; // Campagna indefinita
        }
        
        $oggi = now()->startOfDay();
        $dataFine = $this->data_fine->startOfDay();
        
        if ($dataFine->isPast()) {
            return 0; // Campagna terminata
        }
        
        return $oggi->diffInDays($dataFine);
    }

    public function getTotaleProfitAttribute(): float
    {
        $ricavi = $this->ordini()->sum('prezzo_vendita');
        $costiTotali = $this->ordini()->selectRaw('SUM(costo_marketing + costo_prodotto + costo_spedizione + altri_costi) as totale')->value('totale') ?? 0;
        return $ricavi - $costiTotali - $this->spesa;
    }

    /**
     * Calcola il totale dei costi degli ordini associati
     */
    public function getTotaleCostiAttribute(): float
    {
        return $this->ordini()->selectRaw('SUM(costo_marketing + costo_prodotto + costo_spedizione + altri_costi) as totale')->value('totale') ?? 0;
    }

    /**
     * Calcola il CPA totale includendo anche i costi degli ordini
     */
    public function getCpaTotaleAttribute(): float
    {
        $vendite = $this->getVenditeAttribute();
        $costoTotale = $this->spesa + $this->getTotaleCostiAttribute();
        return $vendite > 0 ? $costoTotale / $vendite : 0;
    }
}