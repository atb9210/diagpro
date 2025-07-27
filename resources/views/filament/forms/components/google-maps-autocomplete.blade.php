<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        apiKey: '{{ \App\Models\Integrazione::get('google_maps_api_key') }}',
        fieldPrefix: '{{ $getFieldPrefix() }}',
        autocomplete: null,
        
        init() {
            console.log('GoogleMapsAutocomplete component initialized');
            console.log('API Key:', this.apiKey);
            console.log('Field Prefix:', this.fieldPrefix);
            this.loadGoogleMaps();
        },
        
        loadGoogleMaps() {
            console.log('Loading Google Maps...');
            if (typeof google !== 'undefined') {
                console.log('Google Maps already loaded, initializing autocomplete');
                this.initializeAutocomplete();
                return;
            }
            
            if (!this.apiKey) {
                console.error('Google Maps API key not found');
                return;
            }
            
            console.log('Loading Google Maps API script...');
            
            // Crea una callback unica per questo componente
            const callbackName = 'initGoogleMaps_' + Math.random().toString(36).substr(2, 9);
            const self = this; // Salva il riferimento al contesto Alpine
            
            window[callbackName] = () => {
                console.log('Google Maps API loaded successfully');
                self.initializeAutocomplete();
                // Pulisci la callback globale
                delete window[callbackName];
            };
            
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${this.apiKey}&libraries=places&callback=${callbackName}`;
            script.async = true;
            script.defer = true;
            
            script.onerror = () => {
                console.error('Failed to load Google Maps API');
                delete window[callbackName];
            };
            
            document.head.appendChild(script);
        },
        
        initializeAutocomplete() {
            console.log('Initializing autocomplete...');
            const input = this.$refs.autocompleteInput;
            console.log('Input element:', input);
            if (!input) {
                console.error('Input element not found');
                // Prova a cercare l'input con un timeout
                setTimeout(() => {
                    const delayedInput = this.$refs.autocompleteInput;
                    console.log('Delayed input search:', delayedInput);
                    if (delayedInput) {
                        this.setupAutocomplete(delayedInput);
                    }
                }, 500);
                return;
            }
            
            this.setupAutocomplete(input);
        },
        
        setupAutocomplete(input) {
            console.log('Setting up autocomplete for input:', input);
            
            try {
                this.autocomplete = new google.maps.places.Autocomplete(input, {
                    types: ['address'],
                    componentRestrictions: { 
                        country: ['it', 'fr', 'de', 'es', 'ch', 'at', 'us', 'gb'] 
                    }
                });
                
                console.log('Autocomplete initialized successfully');
                
                this.autocomplete.addListener('place_changed', () => {
                    console.log('Place changed event triggered');
                    const place = this.autocomplete.getPlace();
                    
                    if (!place.geometry) {
                        console.warn('No geometry found for selected place');
                        return;
                    }
                    
                    console.log('Selected place:', place);
                    
                    // Aggiorna il campo corrente
                    this.state = place.formatted_address;
                    
                    // Estrai i componenti dell'indirizzo
                    this.updateAddressFields(place.address_components);
                });
            } catch (error) {
                console.error('Error initializing autocomplete:', error);
            }
        },
        
        updateAddressFields(components) {
            let address = '';
            let postalCode = '';
            let city = '';
            let province = '';
            let country = '';
            let streetNumber = '';
            let route = '';
            
            components.forEach(component => {
                const types = component.types;
                
                if (types.includes('street_number')) {
                    streetNumber = component.long_name;
                }
                if (types.includes('route')) {
                    route = component.long_name;
                }
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
            
            // Componi l'indirizzo
            if (route) {
                address = route;
                if (streetNumber) {
                    address += ', ' + streetNumber;
                }
            }
            
            // Aggiorna i campi correlati
            this.updateField(`${this.fieldPrefix}_cap`, postalCode);
            this.updateField(`${this.fieldPrefix}_citta`, city);
            this.updateField(`${this.fieldPrefix}_provincia`, province);
            this.updateField(`${this.fieldPrefix}_stato`, country);
        },
        
        updateField(fieldName, value) {
            console.log(`Updating field ${fieldName} with value:`, value);
            
            // Prova diversi selettori per i campi Filament
            const selectors = [
                `input[name="${fieldName}"]`,
                `input[wire\:model="data.${fieldName}"]`,
                `input[x-model="state.${fieldName}"]`,
                `select[name="${fieldName}"]`,
                `select[wire\:model="data.${fieldName}"]`,
                `select[x-model="state.${fieldName}"]`
            ];
            
            let input = null;
            for (const selector of selectors) {
                input = document.querySelector(selector);
                if (input) {
                    console.log(`Found field ${fieldName} with selector: ${selector}`);
                    break;
                }
            }
            
            if (input) {
                if (input.tagName === 'SELECT') {
                    // Per i select, trova l'opzione corrispondente
                    const option = input.querySelector(`option[value="${value}"]`);
                    if (option) {
                        input.value = value;
                    }
                } else {
                    input.value = value;
                }
                
                // Trigger eventi per Filament/Livewire
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Trigger evento Livewire se presente
                if (window.Livewire) {
                    input.dispatchEvent(new CustomEvent('livewire:update'));
                }
                
                console.log(`Field ${fieldName} updated successfully`);
            } else {
                console.warn(`Field ${fieldName} not found with any selector`);
            }
        }
    }">
        <input
            x-ref="autocompleteInput"
            x-model="state"
            type="text"
            class="fi-input block w-full border-none py-1.5 text-base text-gray-950 transition duration-75 placeholder:text-gray-400 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] dark:disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.500)] sm:text-sm sm:leading-6 bg-white/0"
            @if ($getPlaceholder())
                placeholder="{{ $getPlaceholder() }}"
            @endif
            @if ($isRequired())
                required
            @endif
        />
        
        @if (!\App\Models\Integrazione::get('google_maps_api_key'))
            <p class="text-sm text-red-600 mt-1">
                Chiave API Google Maps non configurata nelle impostazioni.
            </p>
        @endif
    </div>
</x-dynamic-component>