<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Impostazione extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'chiave',
        'valore',
        'tipo',
        'descrizione',
    ];
    
    /**
     * Ottieni il valore di un'impostazione per chiave
     */
    public static function get(string $chiave, $default = null)
    {
        $impostazione = static::where('chiave', $chiave)->first();
        
        if (!$impostazione) {
            return $default;
        }
        
        return static::castValue($impostazione->valore, $impostazione->tipo);
    }
    
    /**
     * Imposta il valore di un'impostazione
     */
    public static function set(string $chiave, $valore, string $tipo = 'string', string $descrizione = null)
    {
        return static::updateOrCreate(
            ['chiave' => $chiave],
            [
                'valore' => is_array($valore) || is_object($valore) ? json_encode($valore) : $valore,
                'tipo' => $tipo,
                'descrizione' => $descrizione,
            ]
        );
    }
    
    /**
     * Converte il valore nel tipo corretto
     */
    protected static function castValue($valore, string $tipo)
    {
        return match ($tipo) {
            'number' => (float) $valore,
            'boolean' => (bool) $valore,
            'json' => json_decode($valore, true),
            default => $valore,
        };
    }
}
