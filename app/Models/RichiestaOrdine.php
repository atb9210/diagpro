<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class RichiestaOrdine extends Model
{
    use HasFactory;

    protected $table = 'richieste_ordine';

    protected $fillable = [
        'numero_richiesta',
        'shop_id',
        'cliente_id',
        'ordine_id',
        'totale',
        'totale_spedizione',
        'note',
        'dati_cliente',
        'dati_spedizione',
        'stato',
        'validato_da',
        'validato_il',
        'note_validazione',
        'motivo_rifiuto',
        'ip_origine',
        'user_agent',
        'metadati_aggiuntivi',
    ];

    protected $casts = [
        'validato_il' => 'datetime',
        'dati_cliente' => 'array',
        'dati_spedizione' => 'array',
        'metadati_aggiuntivi' => 'array',
        'totale' => 'decimal:2',
        'totale_spedizione' => 'decimal:2',
    ];

    // Stati possibili
    public const STATO_IN_ATTESA = 'in_attesa_validazione';
    public const STATO_IN_VALIDAZIONE = 'in_validazione';
    public const STATO_APPROVATO = 'approvato';
    public const STATO_RIFIUTATO = 'rifiutato';
    public const STATO_CONVERTITO = 'convertito';

    public static function getStati(): array
    {
        return [
            self::STATO_IN_ATTESA => 'In Attesa Validazione',
            self::STATO_IN_VALIDAZIONE => 'In Validazione',
            self::STATO_APPROVATO => 'Approvato',
            self::STATO_RIFIUTATO => 'Rifiutato',
            self::STATO_CONVERTITO => 'Convertito in Ordine',
        ];
    }

    // Relazioni
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function ordine(): BelongsTo
    {
        return $this->belongsTo(Ordini::class);
    }

    public function validatoDa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validato_da');
    }

    public function prodotti(): BelongsToMany
    {
        return $this->belongsToMany(Prodotto::class, 'richiesta_ordine_prodotto')
            ->withPivot([
                'nome_prodotto',
                'sku',
                'prezzo_unitario',
                'prezzo_personalizzato',
                'quantita',
                'subtotale',
                'configurazione'
            ])
            ->withTimestamps();
    }

    // Metodi di utilità
    public function generaNumeroRichiesta(): string
    {
        $prefisso = 'RQ-' . $this->shop->codice . '-';
        $numero = str_pad($this->id ?? 1, 6, '0', STR_PAD_LEFT);
        return $prefisso . $numero;
    }

    public function puoEssereValidata(): bool
    {
        return in_array($this->stato, [self::STATO_IN_ATTESA, self::STATO_IN_VALIDAZIONE]);
    }

    public function puoEssereApprovata(): bool
    {
        return $this->stato === self::STATO_IN_VALIDAZIONE;
    }

    public function puoEssereRifiutata(): bool
    {
        return in_array($this->stato, [self::STATO_IN_ATTESA, self::STATO_IN_VALIDAZIONE]);
    }

    public function puoEssereConvertita(): bool
    {
        return $this->stato === self::STATO_APPROVATO && !$this->ordine_id;
    }

    public function getStatoColorAttribute(): string
    {
        return match($this->stato) {
            self::STATO_IN_ATTESA => 'warning',
            self::STATO_IN_VALIDAZIONE => 'info',
            self::STATO_APPROVATO => 'success',
            self::STATO_RIFIUTATO => 'danger',
            self::STATO_CONVERTITO => 'primary',
            default => 'gray'
        };
    }

    public function getStatoLabelAttribute(): string
    {
        return self::getStati()[$this->stato] ?? $this->stato;
    }

    // Boot method per generare automaticamente il numero richiesta
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->numero_richiesta)) {
                // Genera un numero temporaneo, verrà aggiornato dopo il salvataggio
                $model->numero_richiesta = 'TEMP-' . Str::random(10);
            }
        });

        static::created(function ($model) {
            if (Str::startsWith($model->numero_richiesta, 'TEMP-')) {
                $model->update([
                    'numero_richiesta' => $model->generaNumeroRichiesta()
                ]);
            }
        });
    }

    // Scope per filtrare per stato
    public function scopeInAttesa($query)
    {
        return $query->where('stato', self::STATO_IN_ATTESA);
    }

    public function scopeInValidazione($query)
    {
        return $query->where('stato', self::STATO_IN_VALIDAZIONE);
    }

    public function scopeApprovate($query)
    {
        return $query->where('stato', self::STATO_APPROVATO);
    }

    public function scopeRifiutate($query)
    {
        return $query->where('stato', self::STATO_RIFIUTATO);
    }

    public function scopeConvertite($query)
    {
        return $query->where('stato', self::STATO_CONVERTITO);
    }

    public function scopePerShop($query, $shopId)
    {
        return $query->where('shop_id', $shopId);
    }
}