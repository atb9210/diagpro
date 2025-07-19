<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Impostazione;

class GoogleMapsAutocomplete extends Component
{
    public $fieldName;
    public $placeholder;
    public $required;
    public $value;
    public $addressComponents = [];
    
    public function mount($fieldName, $placeholder = 'Inserisci indirizzo...', $required = false, $value = null)
    {
        $this->fieldName = $fieldName;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->value = $value;
    }
    
    public function updatedValue($value)
    {
        $this->dispatch('address-selected', [
            'fieldName' => $this->fieldName,
            'value' => $value,
            'components' => $this->addressComponents
        ]);
    }
    
    public function setAddressComponents($components)
    {
        $this->addressComponents = $components;
        
        // Estrai i componenti dell'indirizzo
        $address = '';
        $postalCode = '';
        $city = '';
        $province = '';
        $country = '';
        
        foreach ($components as $component) {
            $types = $component['types'];
            
            if (in_array('street_number', $types)) {
                $streetNumber = $component['long_name'];
            }
            if (in_array('route', $types)) {
                $route = $component['long_name'];
            }
            if (in_array('postal_code', $types)) {
                $postalCode = $component['long_name'];
            }
            if (in_array('locality', $types) || in_array('administrative_area_level_3', $types)) {
                $city = $component['long_name'];
            }
            if (in_array('administrative_area_level_2', $types)) {
                $province = $component['short_name'];
            }
            if (in_array('country', $types)) {
                $country = $component['short_name'];
            }
        }
        
        // Componi l'indirizzo completo
        if (isset($route)) {
            $address = $route;
            if (isset($streetNumber)) {
                $address .= ', ' . $streetNumber;
            }
        }
        
        // Dispatch dell'evento con tutti i componenti
        $this->dispatch('address-components-updated', [
            'address' => $address,
            'postalCode' => $postalCode,
            'city' => $city,
            'province' => $province,
            'country' => $country,
            'fieldPrefix' => str_replace('_indirizzo', '', $this->fieldName)
        ]);
    }
    
    public function render()
    {
        $apiKey = Impostazione::get('google_maps_api_key');
        
        return view('livewire.google-maps-autocomplete', [
            'apiKey' => $apiKey
        ]);
    }
}