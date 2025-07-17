task# Guida Completa allo Sviluppo con Filament 3

Questo documento serve come guida di riferimento completa per lo sviluppo dell'admin panel del progetto Diagpro utilizzando Filament 3. L'obiettivo è standardizzare le pratiche di sviluppo, garantire la coerenza e la manutenibilità del codice.

## 1. Architettura e Concetti Fondamentali

Filament è un ecosistema TALL (Tailwind, Alpine.js, Laravel, Livewire) che permette di costruire rapidamente interfacce di amministrazione complesse con un'esperienza di sviluppo fluida.

### 1.1. Il Panel Builder

Il cuore di ogni applicazione Filament è il **Panel Builder**. <mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="2">2</mcreference> La nostra configurazione principale risiede in `app/Providers/Filament/AdminPanelProvider.php`. Questo file è responsabile per:

- **ID e Path**: Identificazione univoca e URL del pannello.
- **Autenticazione**: Gestione del login e della registrazione.
- **Navigazione**: Definizione dei menu, dei gruppi di navigazione e delle voci.
- **Tema e Asset**: Personalizzazione dell'aspetto grafico e caricamento di CSS/JS custom.
- **Plugin**: Registrazione di plugin di terze parti o custom.

### 1.2. Risorse (Resources)

Le risorse sono la rappresentazione CRUD dei modelli Eloquent. <mcreference link="https://filamentphp.com/docs" index="1">1</mcreference> Ogni risorsa, generata con `php artisan make:filament-resource`, crea una struttura in `app/Filament/Admin/Resources`:

- **`NomeRisorsaResource.php`**: Classe principale che definisce il modello, la form, la tabella, le relazioni e la navigazione.
- **`Pages/`**: Contiene le classi per le pagine standard (`ListRecords`, `CreateRecord`, `EditRecord`, `ViewRecord`).
- **`RelationManagers/`**: Permette di gestire le relazioni Eloquent direttamente dalla pagina di modifica o visualizzazione di una risorsa genitore.

## 2. Componenti dell'Interfaccia Utente

Filament fornisce un set ricco di componenti pre-costruiti per form, tabelle e visualizzazione dati.

### 2.1. Form Builder

I form sono definiti nel metodo `form()` di una risorsa. I principi da seguire sono:

- **Schema Fluente**: Utilizzare la sintassi a catena per definire i campi.
- **Componenti di Campo**: Scegliere tra una vasta gamma di campi (`TextInput`, `Select`, `DatePicker`, `FileUpload`, `Repeater`, etc.).
- **Layout**: Organizzare i campi in sezioni (`Section`), griglie (`Grid`), tab (`Tabs`) per una migliore UX.
- **Validazione**: Le regole di validazione di Laravel possono essere applicate direttamente ai campi.
- **Campi Complessi**: Per logiche complesse, usare i `Repeater` per dati strutturati (es. item di un ordine) e i `Wizard` per form multi-step.

### 2.2. Table Builder

Le tabelle, definite nel metodo `table()` di una risorsa, visualizzano collezioni di record. Le best practice includono:

- **Colonne**: Utilizzare i tipi di colonna appropriati (`TextColumn`, `IconColumn`, `ImageColumn`) per rappresentare i dati.
- **Azioni (Actions)**: Aggiungere azioni per riga (`EditAction`, `DeleteAction`) e azioni globali (`CreateAction`). Le `BulkAction` permettono operazioni su record multipli.
- **Filtri (Filters)**: Implementare filtri per permettere agli utenti di affinare le ricerche. I filtri possono essere semplici (`SelectFilter`) o complessi (`Filter::form(...)`).
- **Ricerca**: Abilitare la ricerca su colonne specifiche per trovare rapidamente i record.

### 2.3. Widget

I widget sono ideali per le dashboard. <mcreference link="https://filamentphp.com/docs" index="1">1</mcreference> Si dividono in:

- **Stats Widgets**: Mostrano statistiche numeriche (es. `StatsOverviewWidget`).
- **Chart Widgets**: Visualizzano grafici (es. `LineChartWidget`, `BarChartWidget`).
- **Table Widgets**: Mostrano una tabella di record recenti o importanti.

## 3. Flusso di Dati e Logica Custom

### 3.1. Lifecycle Hooks

Per intervenire nel ciclo di vita di una richiesta CRUD, Filament espone potenti metodi nelle pagine delle risorse:

- **`mutateFormDataBeforeCreate(array $data): array`**: Modifica i dati del form prima di creare il record.
- **`handleRecordCreation(array $data): Model`**: Sostituisce la logica di creazione standard. Utile per gestire relazioni complesse (es. tabelle pivot).
- **`afterCreate()`**: Esegue codice dopo la creazione del record.
- **`mutateFormDataBeforeFill(array $data): array`**: Pre-popola e formatta i dati prima di mostrarli nel form di modifica.
- **`handleRecordUpdate(Model $record, array $data): Model`**: Sostituisce la logica di aggiornamento standard.
- **`afterUpdate()`**: Esegue codice dopo l'aggiornamento.

### 3.2. Azioni Personalizzate (Custom Actions)

Le azioni possono essere personalizzate per eseguire logiche complesse, aprire modali con form aggiuntivi o reindirizzare l'utente. <mcreference link="https://filamentphp.com/docs" index="1">1</mcreference>

## 4. Convenzioni e Best Practice

1.  **Codice Pulito**: Mantenere i metodi `form()` e `table()` leggibili, estraendo logiche complesse in metodi privati o classi dedicate.
2.  **Autorizzazione**: Utilizzare le policy di Laravel e il metodo `can()` sulle risorse per un controllo granulare degli accessi.
3.  **Localizzazione**: Tutte le etichette, i titoli e i messaggi devono usare le funzioni di traduzione di Laravel (`__('key')`).
4.  **Riutilizzo**: Creare campi di form (`php artisan make:form-field`) o colonne di tabella personalizzate per logiche riutilizzabili in più risorse.

## 5. Ottimizzazione

Per un'applicazione performante in produzione, è fondamentale eseguire i comandi di ottimizzazione di Laravel e Filament durante il deploy: <mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="2">2</mcreference>

```bash
php artisan optimize
php artisan filament:optimize
```

Questi comandi mettono in cache configurazioni, rotte, viste e componenti Filament, riducendo drasticamente i tempi di caricamento. <mcreference link="https://filamentphp.com/docs/3.x/panels/installation" index="2">2</mcreference>