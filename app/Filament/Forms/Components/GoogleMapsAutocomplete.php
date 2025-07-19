<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class GoogleMapsAutocomplete extends TextInput
{
    protected string $view = 'filament.forms.components.google-maps-autocomplete';
    
    protected string $fieldPrefix = '';
    
    public function fieldPrefix(string $prefix): static
    {
        $this->fieldPrefix = $prefix;
        
        return $this;
    }
    
    public function getFieldPrefix(): string
    {
        return $this->fieldPrefix;
    }
}