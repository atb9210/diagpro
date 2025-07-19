<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Illuminate\Support\HtmlString;

class SimpleGoogleMapsAutocomplete extends TextInput
{
    protected string $view = 'filament.forms.components.simple-google-maps-autocomplete';
    
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
    
    public function getExtraInputAttributes(): array
    {
        return array_merge(parent::getExtraInputAttributes(), [
            'data-field-prefix' => $this->getFieldPrefix(),
            'data-google-maps-autocomplete' => 'true',
        ]);
    }
}