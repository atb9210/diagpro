<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="fi-fo-text-input">
        <input
            {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
            {!! $getExtraInputAttributeBag()->class([
                'fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0',
            ]) !!}
            type="text"
            @if ($getPlaceholder())
                placeholder="{{ $getPlaceholder() }}"
            @endif
            @if ($isRequired())
                required
            @endif
        />
    </div>
    
    @if (!\App\Models\Impostazione::get('google_maps_api_key'))
        <p class="text-sm text-red-600 mt-1">
            Chiave API Google Maps non configurata nelle impostazioni.
        </p>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apiKey = '{{ \App\Models\Impostazione::get('google_maps_api_key') }}';
            
            if (!apiKey) {
                console.error('Google Maps API key not found');
                return;
            }
            
            console.log('Initializing Simple Google Maps Autocomplete');
            console.log('API Key:', apiKey);
            
            // Funzione per configurare l'autocompletamento su un singolo input
            function setupAutocompleteForInput(input) {
                let fieldPrefix = input.getAttribute('data-field-prefix');
                
                // Se non ha il data-field-prefix, prova a dedurlo dal wire:model
                if (!fieldPrefix) {
                    const wireModel = input.getAttribute('wire:model');
                    if (wireModel && wireModel.includes('indirizzo_')) {
                        fieldPrefix = wireModel.replace('data.indirizzo_', '').replace('data.', '');
                    }
                }
                
                console.log('Setting up autocomplete for input with prefix:', fieldPrefix);
                
                try {
                    const autocomplete = new google.maps.places.Autocomplete(input, {
                        types: ['address'],
                        componentRestrictions: { 
                            country: ['it', 'fr', 'de', 'es', 'ch', 'at', 'us', 'gb'] 
                        }
                    });
                    
                    console.log('Autocomplete created successfully for:', fieldPrefix);
                    
                    autocomplete.addListener('place_changed', function() {
                        console.log('Place changed event triggered for:', fieldPrefix);
                        const place = autocomplete.getPlace();
                        
                        if (!place.geometry) {
                            console.warn('No geometry found for selected place');
                            return;
                        }
                        
                        console.log('Selected place:', place);
                        
                        // Aggiorna il campo corrente
                        input.value = place.formatted_address;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        
                        // Estrai i componenti dell'indirizzo
                        updateAddressFields(place.address_components, fieldPrefix);
                    });
                    
                } catch (error) {
                    console.error('Error creating autocomplete for', fieldPrefix, ':', error);
                }
            }
            
            // Funzione per inizializzare l'autocompletamento
            function initializeGoogleMaps() {
                console.log('Google Maps API loaded, setting up autocomplete fields');
                
                // Trova tutti i campi con autocompletamento usando selettori multipli
                let autocompleteInputs = document.querySelectorAll('input[data-google-maps-autocomplete="true"]');
                
                // Fallback: cerca per placeholder se non trova campi con attributo
                if (autocompleteInputs.length === 0) {
                    autocompleteInputs = document.querySelectorAll('input[placeholder*="Inizia a digitare"]');
                    console.log('Using fallback selector, found inputs:', autocompleteInputs.length);
                } else {
                    console.log('Found autocomplete inputs:', autocompleteInputs.length);
                }
                
                autocompleteInputs.forEach(function(input) {
                    if (!input.hasAttribute('data-autocomplete-initialized')) {
                        input.setAttribute('data-autocomplete-initialized', 'true');
                        setupAutocompleteForInput(input);
                    }
                });
                
                // Aggiungi un MutationObserver per gestire i campi caricati dinamicamente
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                const newInputs = node.querySelectorAll ? 
                                    node.querySelectorAll('input[placeholder*="Inizia a digitare"], input[data-google-maps-autocomplete="true"]') : 
                                    [];
                                
                                newInputs.forEach(function(input) {
                                    if (!input.hasAttribute('data-autocomplete-initialized')) {
                                        input.setAttribute('data-autocomplete-initialized', 'true');
                                        setupAutocompleteForInput(input);
                                    }
                                });
                            }
                        });
                    });
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
            
            // Funzione per aggiornare i campi dell'indirizzo
            function updateAddressFields(components, fieldPrefix) {
                let postalCode = '';
                let city = '';
                let province = '';
                let country = '';
                
                components.forEach(function(component) {
                    const types = component.types;
                    
                    if (types.includes('postal_code')) {
                        postalCode = component.long_name;
                    }
                    if (types.includes('locality') || types.includes('administrative_area_level_3')) {
                        city = component.long_name;
                    }
                    if (types.includes('administrative_area_level_2')) {
                        province = component.short_name;
                    }
                    if (types.includes('country')) {
                        country = component.short_name;
                    }
                });
                
                // Aggiorna i campi correlati
                updateField(fieldPrefix + '_cap', postalCode);
                updateField(fieldPrefix + '_citta', city);
                updateField(fieldPrefix + '_provincia', province);
                updateField(fieldPrefix + '_stato', country);
            }
            
            // Funzione per aggiornare un singolo campo
            function updateField(fieldName, value) {
                if (!value) return;
                
                console.log('Updating field:', fieldName, 'with value:', value);
                
                // Prova diversi selettori per i campi Filament
                const selectors = [
                    `input[wire\\:model="data.${fieldName}"]`,
                    `select[wire\\:model="data.${fieldName}"]`,
                    `input[name="${fieldName}"]`,
                    `select[name="${fieldName}"]`
                ];
                
                let field = null;
                for (const selector of selectors) {
                    field = document.querySelector(selector);
                    if (field) {
                        console.log('Found field', fieldName, 'with selector:', selector);
                        break;
                    }
                }
                
                if (field) {
                    if (field.tagName === 'SELECT') {
                        // Per i select, trova l'opzione corrispondente
                        const option = field.querySelector(`option[value="${value}"]`);
                        if (option) {
                            field.value = value;
                        }
                    } else {
                        field.value = value;
                    }
                    
                    // Trigger eventi per Livewire
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    console.log('Field', fieldName, 'updated successfully');
                } else {
                    console.warn('Field', fieldName, 'not found');
                }
            }
            
            // Carica l'API Google Maps se non è già caricata
            if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                console.log('Google Maps API already loaded');
                // Aggiungi un piccolo delay per permettere a Livewire di caricare i campi
                setTimeout(initializeGoogleMaps, 500);
            } else {
                console.log('Loading Google Maps API...');
                
                // Crea una callback globale unica
                const callbackName = 'initGoogleMaps_' + Date.now();
                window[callbackName] = function() {
                    console.log('Google Maps API loaded via callback');
                    initializeGoogleMaps();
                    delete window[callbackName];
                };
                
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&callback=${callbackName}`;
                script.async = true;
                script.defer = true;
                
                script.onerror = function() {
                    console.error('Failed to load Google Maps API');
                    delete window[callbackName];
                };
                
                document.head.appendChild(script);
            }
        });
    </script>
    @endpush
</x-dynamic-component>