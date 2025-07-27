<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integrazione extends Model
{
    use HasFactory;

    protected $table = 'integraziones';

    protected $fillable = [
        'chiave',
        'valore',
        'tipo',
        'descrizione',
    ];

    protected $casts = [
        'valore' => 'string',
    ];

    /**
     * Get the value attribute based on type
     */
    public function getValoreAttribute($value)
    {
        return match ($this->tipo) {
            'boolean' => (bool) $value,
            'number' => is_numeric($value) ? (float) $value : $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set the value attribute based on type
     */
    public function setValoreAttribute($value)
    {
        $this->attributes['valore'] = match ($this->tipo) {
            'boolean' => $value ? '1' : '0',
            'json' => is_array($value) ? json_encode($value) : $value,
            default => (string) $value,
        };
    }

    /**
     * Get a setting value by key
     */
    public static function get($key, $default = null)
    {
        $setting = static::where('chiave', $key)->first();
        return $setting ? $setting->valore : $default;
    }

    /**
     * Set a setting value by key
     */
    public static function set($key, $value, $tipo = 'string', $descrizione = null)
    {
        return static::updateOrCreate(
            ['chiave' => $key],
            [
                'valore' => $value,
                'tipo' => $tipo,
                'descrizione' => $descrizione
            ]
        );
    }
}