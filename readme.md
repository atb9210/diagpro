# DiagPro - Sistema di Gestione Diagnostica

## Versione 1.0.3

### 🚀 Panoramica

DiagPro è un sistema completo di gestione diagnostica sviluppato con Laravel 12 e Filament 3. Il sistema offre una soluzione integrata per la gestione di clienti, prodotti, ordini, campagne marketing e integrazioni di sistema.

### 📋 Funzionalità Principali

#### 🏢 Gestione Core Business
- ✅ **Clienti** (`/admin/clientes`) - Gestione anagrafica clienti
- ✅ **Prodotti** (`/admin/prodottos`) - Catalogo prodotti con categorie e fornitori
- ✅ **Ordini** (`/admin/ordinis`) - Sistema completo di gestione ordini
- ✅ **Abbonamenti** (`/admin/abbonamentos`) - Gestione abbonamenti ricorrenti
- ✅ **Spedizioni** (`/admin/spediziones`) - Tracking e gestione spedizioni

#### 📊 Marketing e Analytics
- ✅ **Campagne** (`/admin/campagnas`) - Gestione campagne marketing
- ✅ **Traffic Sources** (`/admin/traffic-sources`) - Analisi sorgenti di traffico
- ✅ **Ricorrenze Attive** (`/admin/ricorrenze-attives`) - Monitoraggio abbonamenti attivi
- ✅ **Richieste Ordine** (`/admin/richieste-ordine`) - Gestione richieste personalizzate

#### 🔧 Sistema e Configurazione
- ✅ **Integrazioni** (`/admin/integraziones`) - Configurazioni e integrazioni di sistema
- ✅ **Shops** (`/admin/shops`) - Gestione multi-shop
- ✅ **Categorie** (`/admin/categorias`) - Categorizzazione prodotti
- ✅ **Fornitori** (`/admin/fornitoris`) - Gestione fornitori

### 🛠️ Stack Tecnologico

- **Backend:** Laravel 12
- **Admin Panel:** Filament 3
- **Frontend Interattivo:** Livewire 3
- **Database:** MySQL
- **Styling:** Tailwind CSS
- **Deployment:** Docker + Nginx

### 📦 Installazione

1. **Clone del repository:**
   ```bash
   git clone <repository-url>
   cd diagpro
   ```

2. **Installazione dipendenze:**
   ```bash
   composer install
   npm install
   ```

3. **Configurazione ambiente:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup database:**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Avvio sviluppo:**
   ```bash
   php artisan serve
   npm run dev
   ```

### 🔧 Configurazione

#### Integrazioni di Sistema
1. Accedi al pannello admin: `/admin`
2. Vai su **Integrazioni** (`/admin/integraziones`)
3. Configura le integrazioni necessarie:
   - API Keys
   - Servizi esterni
   - Configurazioni di sistema

#### Configurazione Multi-Shop
1. Configura gli shop in `/admin/shops`
2. Associa prodotti ai rispettivi shop
3. Gestisci ordini per shop specifici

### 📊 Dashboard e Analytics

Il sistema include dashboard avanzate con:
- Widget di statistiche in tempo reale
- Grafici di performance vendite
- Monitoraggio campagne marketing
- Analytics traffico e conversioni

### 🔐 Sicurezza

- Autenticazione multi-livello
- Autorizzazioni basate su ruoli
- Protezione CSRF
- Validazione input avanzata
- Logging completo delle attività

### 🚀 Deployment

#### Docker (Raccomandato)
```bash
docker-compose up -d
```

#### Deployment Manuale
1. Configura server web (Nginx/Apache)
2. Setup PHP 8.2+
3. Configura database MySQL
4. Deploy codice e run migrations

### 📝 Changelog

#### Versione 1.0.3 (Corrente)
- **BREAKING CHANGE:** Refactor completo da "Impostazioni" a "Integrazioni"
- Eliminato modello `Impostazione` deprecato
- Creato nuovo modello `Integrazione` più coerente
- Aggiornati tutti i riferimenti e collegamenti
- Migliorata architettura del sistema di configurazione
- Ottimizzata user experience del pannello admin

#### Versione 1.0.2
- Aggiunta gestione multi-shop
- Implementato sistema di ricorrenze
- Migliorati widget dashboard

#### Versione 1.0.1
- Sistema di spedizioni completo
- Integrazione con servizi esterni
- Ottimizzazioni performance

### 🤝 Contributi

Per contribuire al progetto:
1. Fork del repository
2. Crea feature branch
3. Commit delle modifiche
4. Push e creazione Pull Request

### 📞 Supporto

Per supporto tecnico o domande:
- Documentazione: `/docs`
- Issues: GitHub Issues
- Email: support@diagpro.com

### 📄 Licenza

Questo progetto è rilasciato sotto licenza MIT. Vedi il file `LICENSE` per dettagli.

---

**DiagPro v1.0.3** - Sistema di Gestione Diagnostica Avanzato