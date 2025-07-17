<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TrafficSource extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nome',
        'icona',
        'attivo'
    ];
    
    /**
     * Get the clients associated with this traffic source.
     */
    public function clienti(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }
    
    /**
     * Get the URL for the icon.
     */
    public function getIconaUrlAttribute()
    {
        if (!$this->icona) {
            return url('/images/default-icon.svg');
        }
        
        return Storage::url($this->icona);
    }
}
