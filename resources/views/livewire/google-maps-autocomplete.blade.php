<div>
    <input 
        type="text" 
        id="{{ $fieldName }}_autocomplete"
        wire:model="value"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 disabled:opacity-50"
    />
    
    @if($apiKey)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof google === 'undefined') {
                    // Carica l'API di Google Maps se non è già caricata
                    const script = document.createElement('script');
                    script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $apiKey }}&libraries=places&callback=initAutocomplete{{ $fieldName }}';
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);
                    
                    window.initAutocomplete{{ $fieldName }} = function() {
                        initializeAutocomplete();
                    };
                } else {
                    initializeAutocomplete();
                }
                
                function initializeAutocomplete() {
                    const input = document.getElementById('{{ $fieldName }}_autocomplete');
                    if (!input) return;
                    
                    const autocomplete = new google.maps.places.Autocomplete(input, {
                        types: ['address'],
                        componentRestrictions: { country: ['it', 'fr', 'de', 'es', 'ch', 'at', 'us', 'gb'] }
                    });
                    
                    autocomplete.addListener('place_changed', function() {
                        const place = autocomplete.getPlace();
                        
                        if (!place.geometry) {
                            return;
                        }
                        
                        // Aggiorna il valore del campo
                        input.value = place.formatted_address;
                        input.dispatchEvent(new Event('input'));
                        
                        // Invia i componenti dell'indirizzo al componente Livewire
                        @this.call('setAddressComponents', place.address_components);
                    });
                }
            });
        </script>
    @else
        <p class="text-sm text-red-600 mt-1">Chiave API Google Maps non configurata nelle impostazioni.</p>
    @endif
    
    <script>
        // Listener per aggiornare i campi del form quando viene selezionato un indirizzo
        document.addEventListener('livewire:init', () => {
            Livewire.on('address-components-updated', (data) => {
                const components = data[0];
                const prefix = components.fieldPrefix;
                
                // Aggiorna i campi del form
                const fields = {
                    [`${prefix}_indirizzo`]: components.address,
                    [`${prefix}_cap`]: components.postalCode,
                    [`${prefix}_citta`]: components.city,
                    [`${prefix}_provincia`]: components.province,
                    [`${prefix}_stato`]: components.country
                };
                
                Object.entries(fields).forEach(([fieldName, value]) => {
                    const field = document.querySelector(`[wire\\:model="${fieldName}"], [name="${fieldName}"]`);
                    if (field && value) {
                        field.value = value;
                        field.dispatchEvent(new Event('input'));
                        field.dispatchEvent(new Event('change'));
                    }
                });
            });
        });
    </script>
</div>