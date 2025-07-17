# Regole e Convenzioni per lo Sviluppo con Laravel 12

Questo documento definisce le linee guida e le best practice per lo sviluppo dell'applicazione utilizzando il framework Laravel 12. L'obiettivo è garantire che il codice sia pulito, manutenibile, scalabile e sicuro. <mcreference link="https://laravel.com/docs/12.x/documentation" index="1">1</mcreference>

## 1. Struttura delle Directory Principali

La struttura del progetto segue le convenzioni di Laravel. <mcreference link="https://laravel.com/docs/11.x/" index="5">5</mcreference> Le directory più importanti sono:

- **`app/`**: Contiene il codice sorgente dell'applicazione (Modelli, Controller, Provider, etc.).
- **`config/`**: Contiene tutti i file di configurazione dell'applicazione. <mcreference link="https://laravel.com/docs/11.x/" index="5">5</mcreference>
- **`database/`**: Contiene le migrazioni, i seeder e le factory per il database.
- **`public/`**: È la document root del server web e contiene i file accessibili pubblicamente (assets compilati, `index.php`).
- **`resources/`**: Contiene le viste (Blade), i file di lingua e gli asset non compilati (CSS, JS).
- **`routes/`**: Contiene tutte le definizioni delle rotte (`web.php`, `api.php`).
- **`storage/`**: Contiene i file generati dal framework (log, cache, file caricati).
- **`tests/`**: Contiene i test automatici (Unit e Feature).

## 2. Componenti Chiave di Laravel 12

### 2.1. Routing

Le rotte sono definite nei file dentro la directory `routes/`. Per le applicazioni web tradizionali, si utilizza principalmente `web.php`, che applica di default il middleware group `web` (sessioni, CSRF protection, etc.).

### 2.2. Controller

I controller si trovano in `app/Http/Controllers` e gestiscono la logica delle richieste HTTP. È buona norma mantenere i controller "snelli" (thin controllers), delegando la logica di business a classi di servizio o ai modelli stessi.

### 2.3. Modelli (Eloquent ORM)

I modelli Eloquent si trovano in `app/Models` e rappresentano le tabelle del database. Definiscono le relazioni, gli attributi e la logica di business legata a una specifica entità.

### 2.4. Viste (Blade)

Le viste, scritte con il motore di templating Blade, si trovano in `resources/views`. Blade offre direttive potenti per creare layout riutilizzabili e componenti.

### 2.5. Middleware

I middleware, situati in `app/Http/Middleware`, forniscono un meccanismo per ispezionare e filtrare le richieste HTTP in entrata. Sono registrati nel file `app/Http/Kernel.php`.

### 2.6. Service Provider

I Service Provider sono il punto centrale per il bootstrap di un'applicazione Laravel. Si trovano in `app/Providers` e vengono utilizzati per registrare binding nel service container, registrare event listener, middleware e rotte.

### 2.7. Policy

Le Policy, situate in `app/Policies`, definiscono le regole di autorizzazione per le azioni su un modello.

### 2.8. Starter Kits

Laravel 12 introduce nuovi starter kit per React, Vue e Livewire, che sostituiscono Breeze e Jetstream. <mcreference link="https://laravel.com/docs/12.x/releases" index="2">2</mcreference>

## 3. Convenzioni di Sviluppo

1.  **Nomenclatura:** Seguire scrupolosamente le convenzioni di nomenclatura di Laravel per classi, metodi, tabelle del database e variabili.
2.  **File di Ambiente (`.env`):** Tutte le configurazioni che variano tra gli ambienti (database, chiavi API, etc.) devono essere definite nel file `.env`. Non committare mai il file `.env` nel version control.
3.  **Sicurezza:** Utilizzare le funzionalità di sicurezza integrate di Laravel, come la protezione CSRF, la validazione dell'input e l'escape dell'output (Blade lo fa di default) per prevenire vulnerabilità comuni.
4.  **Test:** Scrivere test (sia unitari che di funzionalità) per le nuove funzionalità per garantire la stabilità e la manutenibilità del codice.

## 4. Artisan CLI

La command-line interface (CLI) di Laravel, `artisan`, è uno strumento fondamentale per lo sviluppo. Utilizzarla per:

- Creare classi (controller, modelli, migrazioni, etc.).
- Eseguire migrazioni e seeder.
- Gestire la cache.
- Eseguire comandi personalizzati.