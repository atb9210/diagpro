# 🏥 Diagpro - Sistema Gestione Ordini e Clienti

**Versione MVP 1.0.0** - Sistema completo per la gestione di ordini, clienti e prodotti sviluppato con Laravel 12 e Filament 3.

## ✨ Funzionalità

- 📊 **Dashboard** - Panoramica generale e statistiche
- 👥 **Gestione Clienti** - Anagrafica completa con indirizzi e sorgenti traffico
- 📦 **Prodotti** - Catalogo con categorie, fornitori e inventario
- 🛒 **Ordini** - Gestione completa ordini e stati pagamento
- 🚚 **Spedizioni** - Tracciamento e gestione consegne
- 🔄 **Abbonamenti** - Sistema abbonamenti ricorrenti
- ⚙️ **Configurazioni** - Impostazioni sistema e API Google Maps

## 🚀 Installazione

```bash
# Clona e installa
git clone <repository-url> diagpro
cd diagpro
composer install && npm install

# Configura
cp .env.example .env
php artisan key:generate

# Database (configura .env prima)
php artisan migrate && php artisan db:seed

# Avvia
npm run build
php artisan serve
```

**Admin:** http://localhost:8000/admin (admin@diagpro.com / password)

## 🛠️ Stack

- Laravel 12 + Filament 3 + Livewire 3
- MySQL 8.0 + Tailwind CSS + Vite

## 📚 Documentazione

| Documento | Descrizione |
|-----------|-------------|
| [📋 VERSION_MVP.md](VERSION_MVP.md) | **Panoramica completa funzionalità MVP** |
| [🔄 GITHUB_VERSIONING.md](GITHUB_VERSIONING.md) | **Guida gestione versioni su GitHub** |
| [📝 CHANGELOG.md](CHANGELOG.md) | **Storico modifiche e versioni** |
| [📁 .trae/rules/](/.trae/rules/) | **Regole sviluppo e convenzioni** |

## 🏗️ Architettura

### Stack Tecnologico

- **Backend:** Laravel 12.x
- **Admin Panel:** Filament 3.x
- **Database:** MySQL 8.0+
- **Frontend:** Livewire 3.x + Alpine.js
- **Styling:** Tailwind CSS (via Filament)
- **Maps:** Google Maps JavaScript API
- **Icons:** Heroicons

### Struttura Progetto

```
app/
├── Filament/Admin/Resources/     # Resource Filament per CRUD
├── Models/                       # Modelli Eloquent
├── Livewire/                    # Componenti Livewire custom
└── Providers/                   # Service Providers

database/
├── migrations/                  # Migrazioni database (15+)
└── seeders/                    # Seeder per dati iniziali

resources/
├── views/filament/             # Template Filament personalizzati
└── css/                        # Stili custom
```

## 🎯 Funzionalità Implementate

### 👥 Gestione Clienti (`/admin/clientes`)
- ✅ Anagrafica completa (nome, email, telefono, indirizzo)
- ✅ Integrazione Google Maps per autocompletamento indirizzi
- ✅ Tracciamento sorgenti traffico
- ✅ Validazione dati e form intuitivi

### 📦 Gestione Prodotti (`/admin/prodottos`)
- ✅ Catalogo prodotti con categorie e fornitori
- ✅ Gestione inventario (quantità, stati)
- ✅ Prezzi e costi
- ✅ Date arrivo prodotti
- ✅ Upload immagini

### 📋 Sistema Ordini (`/admin/ordinis`)
- ✅ Creazione ordini con prodotti e abbonamenti
- ✅ Stati ordine (Pending, Processing, Completed, Cancelled)
- ✅ Stati pagamento (Pending, Paid, Failed, Refunded)
- ✅ Calcolo automatico totali
- ✅ Associazione clienti

### 🚚 Gestione Spedizioni (`/admin/spediziones`)
- ✅ Tracking spedizioni con codici
- ✅ Gestione corrieri
- ✅ Stati spedizione
- ✅ Date spedizione e consegna

### 💳 Abbonamenti (`/admin/abbonamentos`)
- ✅ Piani abbonamento con durata
- ✅ Gestione costi ricorrenti
- ✅ Associazione con ordini
- ✅ Note personalizzabili

### 🏷️ Organizzazione
- ✅ **Categorie** (`/admin/categorias`) - Classificazione prodotti
- ✅ **Fornitori** (`/admin/fornitoris`) - Anagrafica fornitori
- ✅ **Traffic Sources** (`/admin/traffic-sources`) - Canali marketing
- ✅ **Impostazioni** (`/admin/impostaziones`) - Configurazioni sistema

## 🔧 Configurazione

### Variabili Ambiente Principali

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=diagpro
DB_USERNAME=root
DB_PASSWORD=

# Google Maps (opzionale)
GOOGLE_MAPS_API_KEY=your_api_key_here

# Cache e Sessioni
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
```

### Google Maps Setup (Opzionale)

1. Ottieni API Key da [Google Cloud Console](https://console.cloud.google.com/)
2. Abilita **Places API** e **Maps JavaScript API**
3. Aggiungi la chiave in `.env`: `GOOGLE_MAPS_API_KEY=your_key`
4. Configura in `/admin/impostaziones`

## 🚀 Deployment

### Ambiente Produzione

```bash
# 1. Ottimizzazioni
composer install --optimize-autoloader --no-dev
npm run build

# 2. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Migrazioni
php artisan migrate --force

# 4. Permessi storage
chmod -R 775 storage bootstrap/cache
```

### Server Requirements

- **PHP Extensions:** BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- **Database:** MySQL 8.0+ o MariaDB 10.3+
- **Web Server:** Apache 2.4+ o Nginx 1.15+

## 🔄 Versioning

Questo progetto segue il [Semantic Versioning](https://semver.org/):

- **v1.0.0** - Release MVP attuale
- **v1.x.x** - Patch e miglioramenti minori
- **v2.0.0** - Prossima major release

Per dettagli su come gestire le versioni, consulta [GITHUB_VERSIONING.md](GITHUB_VERSIONING.md).

## 🛣️ Roadmap

### v1.1.0 - Sistema Notifiche
- [ ] Notifiche email automatiche
- [ ] Template email personalizzabili
- [ ] Log notifiche

### v1.2.0 - Analytics
- [ ] Dashboard analytics
- [ ] Report vendite
- [ ] Grafici performance

### v1.3.0 - API
- [ ] API REST completa
- [ ] Documentazione API
- [ ] Rate limiting

### v2.0.0 - Advanced Features
- [ ] Sistema permessi granulari
- [ ] Multi-tenancy
- [ ] Integrazione pagamenti
- [ ] App mobile

## 🤝 Contribuire

1. **Fork** del repository
2. **Crea** feature branch (`git checkout -b feature/nuova-funzionalita`)
3. **Commit** modifiche (`git commit -m 'feat: Aggiunta nuova funzionalità'`)
4. **Push** branch (`git push origin feature/nuova-funzionalita`)
5. **Apri** Pull Request

### Convenzioni

- Usa [Conventional Commits](https://www.conventionalcommits.org/)
- Segui le regole in [.trae/rules/](/.trae/rules/)
- Testa sempre le modifiche
- Aggiorna documentazione se necessario

## 📄 License

Questo progetto è rilasciato sotto licenza [MIT](https://opensource.org/licenses/MIT).

## 🆘 Supporto

- **Documentazione:** Consulta i file in [.trae/rules/](/.trae/rules/)
- **Issues:** Apri issue su GitHub per bug o richieste
- **Discussioni:** Usa GitHub Discussions per domande generali

---

**Sviluppato con ❤️ usando Laravel 12 + Filament 3**
