<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $shop->nome) - {{ $shop->meta_title ?? $shop->nome }}</title>
    <meta name="description" content="{{ $shop->meta_description ?? $shop->descrizione }}">
    
    <!-- Filament Styles -->
    @filamentStyles
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Custom Shop Styles -->
    <style>
        :root {
            --shop-primary: {{ $shop->colore_primario ?? '#3B82F6' }};
            --shop-secondary: {{ $shop->colore_secondario ?? '#1E40AF' }};
        }
        
        .shop-primary {
            background-color: var(--shop-primary);
        }
        
        .shop-primary-text {
            color: var(--shop-primary);
        }
        
        .shop-secondary {
            background-color: var(--shop-secondary);
        }
        
        .shop-secondary-text {
            color: var(--shop-secondary);
        }
        
        .btn-shop-primary {
            background-color: var(--shop-primary);
            border-color: var(--shop-primary);
            color: white;
        }
        
        .btn-shop-primary:hover {
            background-color: var(--shop-secondary);
            border-color: var(--shop-secondary);
        }
        
        .cart-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .cart-sidebar.open {
            right: 0;
        }
        
        .cart-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            display: none;
        }
        
        .cart-overlay.show {
            display: block;
        }
        
        @media (max-width: 768px) {
            .cart-sidebar {
                width: 100%;
                right: -100%;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo e Nome Shop -->
                <div class="flex items-center">
                    <a href="{{ route('shop.index', $shop->slug) }}" class="flex items-center space-x-3">
                        @if($shop->logo)
                            <img src="{{ Storage::url($shop->logo) }}" alt="{{ $shop->nome }}" class="h-10 w-10 rounded-full object-cover">
                        @endif
                        <h1 class="text-xl font-bold shop-primary-text">{{ $shop->nome }}</h1>
                    </a>
                </div>
                
                <!-- Carrello -->
                <div class="flex items-center space-x-4">
                    <button id="cart-toggle" class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 21a2 2 0 100-4 2 2 0 000 4zM9 21a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                        <span id="cart-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-white border-t mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; {{ date('Y') }} {{ $shop->nome }}. Tutti i diritti riservati.</p>
                @if($shop->descrizione)
                    <p class="mt-2 text-sm">{{ $shop->descrizione }}</p>
                @endif
            </div>
        </div>
    </footer>
    
    <!-- Cart Sidebar -->
    <div id="cart-overlay" class="cart-overlay"></div>
    <div id="cart-sidebar" class="cart-sidebar">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold">Carrello</h2>
                <button id="cart-close" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="cart-items">
                <!-- Cart items will be loaded here -->
            </div>
            
            <div class="mt-6 pt-6 border-t">
                <div class="flex justify-between items-center mb-4">
                    <span class="font-semibold">Totale:</span>
                    <span id="cart-total" class="font-bold text-lg shop-primary-text">€ 0,00</span>
                </div>
                
                <div class="space-y-2">
                    <button id="continue-shopping" class="w-full py-2 px-4 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Continua con il negozio
                    </button>
                    <a href="{{ route('shop.carrello', $shop->slug) }}" class="block w-full py-2 px-4 btn-shop-primary rounded-md text-center font-medium transition-colors">
                        Vai al carrello
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Added Modal -->
    <div id="product-added-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Prodotto aggiunto al carrello!</h3>
                <p class="text-sm text-gray-500 mb-6">Il prodotto è stato aggiunto con successo al tuo carrello.</p>
                
                <div class="flex space-x-3">
                    <button id="modal-continue" class="flex-1 py-2 px-4 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Continua con il negozio
                    </button>
                    <button id="modal-cart" class="flex-1 py-2 px-4 btn-shop-primary rounded-md font-medium transition-colors">
                        Vai al carrello
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filament Scripts -->
    @filamentScripts
    
    <!-- Custom Scripts -->
    <script>
        // Cart functionality
        let cartCount = 0;
        
        // Toggle cart sidebar
        document.getElementById('cart-toggle').addEventListener('click', function() {
            document.getElementById('cart-sidebar').classList.add('open');
            document.getElementById('cart-overlay').classList.add('show');
            loadCartItems();
        });
        
        // Close cart sidebar
        function closeCart() {
            document.getElementById('cart-sidebar').classList.remove('open');
            document.getElementById('cart-overlay').classList.remove('show');
        }
        
        document.getElementById('cart-close').addEventListener('click', closeCart);
        document.getElementById('cart-overlay').addEventListener('click', closeCart);
        document.getElementById('continue-shopping').addEventListener('click', closeCart);
        
        // Product added modal
        document.getElementById('modal-continue').addEventListener('click', function() {
            document.getElementById('product-added-modal').classList.add('hidden');
        });
        
        document.getElementById('modal-cart').addEventListener('click', function() {
            document.getElementById('product-added-modal').classList.add('hidden');
            document.getElementById('cart-sidebar').classList.add('open');
            document.getElementById('cart-overlay').classList.add('show');
            loadCartItems();
        });
        
        // Add to cart function
        function addToCart(prodottoId, quantita = 1) {
            fetch(`{{ route('shop.carrello.aggiungi', $shop->slug) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    prodotto_id: prodottoId,
                    quantita: quantita
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCartCount(data.carrello_count);
                    loadCartItems(); // Ricarica gli elementi del carrello nella sidebar
                    document.getElementById('product-added-modal').classList.remove('hidden');
                    document.getElementById('product-added-modal').classList.add('flex');
                } else {
                    alert(data.message || 'Errore durante l\'aggiunta al carrello');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Errore durante l\'aggiunta al carrello');
            });
        }
        
        // Update cart count
        function updateCartCount(count) {
            cartCount = count;
            document.getElementById('cart-count').textContent = count;
        }
        
        // Load cart items
        function loadCartItems() {
            const cartItemsContainer = document.getElementById('cart-items');
            const cartTotalElement = document.getElementById('cart-total');
            
            // Mostra un indicatore di caricamento
            cartItemsContainer.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-gray-900"></div></div>';
            
            fetch(`{{ route('shop.carrello', $shop->slug) }}`)
                .then(response => response.text())
                .then(html => {
                    // Estrai i dati del carrello dalla risposta HTML
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Se il carrello è vuoto
                    if (doc.querySelector('.text-center.py-12')) {
                        cartItemsContainer.innerHTML = `
                            <div class="text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l-1.5 6m0 0h9M17 21a2 2 0 100-4 2 2 0 000 4zM9 21a2 2 0 100-4 2 2 0 000 4z"></path>
                                </svg>
                                <p class="text-gray-500">Il tuo carrello è vuoto</p>
                            </div>
                        `;
                        cartTotalElement.textContent = '€ 0,00';
                        return;
                    }
                    
                    // Estrai gli elementi del carrello
                    const items = [];
                    doc.querySelectorAll('.divide-y.divide-gray-200 > div').forEach(itemElement => {
                        const nome = itemElement.querySelector('.font-semibold.text-gray-900').textContent.trim();
                        const prezzo = itemElement.querySelector('.col-span-2.text-center .font-semibold').textContent.trim();
                        const quantita = itemElement.querySelector('.w-8.text-center.font-semibold').textContent.trim();
                        const totaleItem = itemElement.querySelector('.font-bold.text-primary').textContent.trim();
                        
                        let immagine = '';
                        const imgElement = itemElement.querySelector('img');
                        if (imgElement) {
                            immagine = imgElement.getAttribute('src');
                        }
                        
                        items.push({ nome, prezzo, quantita, totaleItem, immagine });
                    });
                    
                    // Estrai il totale del carrello
                    const totale = doc.querySelector('#totale-carrello').textContent.trim();
                    cartTotalElement.textContent = totale;
                    
                    // Renderizza gli elementi del carrello nella sidebar
                    cartItemsContainer.innerHTML = items.map(item => `
                        <div class="py-3 border-b">
                            <div class="flex items-center space-x-3">
                                ${item.immagine ? `<img src="${item.immagine}" alt="${item.nome}" class="w-12 h-12 object-cover rounded">` : 
                                `<div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>`}
                                <div class="flex-1">
                                    <h4 class="font-medium text-sm">${item.nome}</h4>
                                    <div class="flex justify-between text-sm text-gray-500 mt-1">
                                        <span>${item.prezzo} × ${item.quantita}</span>
                                        <span class="font-semibold shop-primary-text">${item.totaleItem}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(error => {
                    console.error('Error loading cart items:', error);
                    cartItemsContainer.innerHTML = '<div class="text-center py-4 text-red-500">Errore nel caricamento del carrello</div>';
                });
        }
        
        // Initialize cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadInitialCartCount();
        });
        
        // Load initial cart count
        function loadInitialCartCount() {
            fetch(`{{ route('shop.carrello.conteggio', $shop->slug) }}`)
                .then(response => response.json())
                .then(data => {
                    updateCartCount(data.carrello_count);
                    loadCartItems(); // Carica anche gli elementi del carrello nella sidebar
                })
                .catch(error => {
                    console.error('Error loading cart count:', error);
                });
        }
    </script>
    
    @stack('scripts')
</body>
</html>