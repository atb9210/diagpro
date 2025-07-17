# Regole e Convenzioni per lo Sviluppo con Livewire 3

Questo documento definisce le linee guida e le best practice per lo sviluppo di componenti interattivi con Livewire 3 all'interno dell'applicazione. L'obiettivo è garantire coerenza, manutenibilità e prestazioni ottimali.

## 1. Principi Fondamentali di Livewire

Livewire permette di costruire interfacce dinamiche utilizzando principalmente PHP, riducendo la necessità di scrivere JavaScript complesso. <mcreference link="https://livewire.laravel.com/" index="3">3</mcreference> <mcreference link="https://laravel-livewire.com/" index="4">4</mcreference>

- **Component-Based**: L'interfaccia utente è suddivisa in componenti riutilizzabili, ognuno con una propria classe PHP e una vista Blade.
- **Stateful**: I componenti mantengono il loro stato (proprietà pubbliche) tra le interazioni dell'utente.
- **Reactive**: Le azioni dell'utente (es. click di un pulsante, input in un form) attivano metodi nella classe del componente, che aggiornano lo stato e ri-renderizzano automaticamente la vista. <mcreference link="https://livewire.laravel.com/docs" index="1">1</mcreference>
- **Server-Side Rendering**: Il rendering iniziale avviene lato server, garantendo una buona SEO. Le interazioni successive avvengono tramite richieste AJAX (chiamate "roundtrip"). <mcreference link="https://laravel-livewire.com/" index="4">4</mcreference>

## 2. Struttura di un Componente Livewire

Ogni componente Livewire è composto da due file principali, generati con il comando `php artisan make:livewire NomeComponente`. <mcreference link="https://livewire.laravel.com/docs" index="1">1</mcreference>

- **Classe del Componente**: `app/Livewire/NomeComponente.php`
  - Estende `Livewire\Component`.
  - Contiene le proprietà pubbliche che definiscono lo stato del componente.
  - Contiene i metodi pubblici che definiscono le azioni del componente.
  - Il metodo `render()` restituisce la vista Blade associata. <mcreference link="https://livewire.laravel.com/docs" index="1">1</mcreference>

- **Vista del Componente**: `resources/views/livewire/nome-componente.blade.php`
  - Contiene il markup HTML del componente.
  - **Deve avere un singolo elemento radice** (solitamente un `<div>`). <mcreference link="https://livewire.laravel.com/docs" index="1">1</mcreference>
  - Utilizza la sintassi Blade per visualizzare le proprietà e le direttive di Livewire (`wire:`) per legare le azioni. <mcreference link="https://livewire.laravel.com/docs" index="1">1</mcreference>

## 3. Convenzioni di Sviluppo

### Gestione dello Stato (Proprietà)
- **Proprietà Pubbliche**: Sono lo stato del componente, accessibili e modificabili sia dalla classe che dalla vista. Sono serializzate e inviate tra il client e il server ad ogni interazione.
- **Proprietà Calcolate**: Utilizzare le Computed Properties per derivare valori da altre proprietà. Questo ottimizza le prestazioni evitando calcoli ripetuti. <mcreference link="https://github.com/livewire/docs" index="5">5</mcreference>

### Azioni (Metodi)
- **Metodi Pubblici**: Sono le azioni che possono essere invocate dalla vista tramite direttive come `wire:click`.
- **Parametri**: I metodi possono accettare parametri direttamente dalla vista (es. `wire:click="salva(123)"`).

### Data Binding
- **`wire:model`**: Per il binding bidirezionale tra un input di un form e una proprietà pubblica.
- **`wire:model.live`**: Aggiorna la proprietà in tempo reale mentre l'utente digita (usare con cautela per non sovraccaricare il server).
- **`wire:model.blur`** (default in v3): Aggiorna la proprietà quando l'input perde il focus.

### Rendering
- **Layout**: I componenti a pagina intera utilizzano un layout Blade definito in `resources/views/components/layouts/app.blade.php`. <mcreference link="https://livewire.laravel.com/docs" index="1">1</mcreference>
- **`$slot`**: Il contenuto del componente viene iniettato nella variabile `$slot` del layout. <mcreference link="https://livewire.laravel.com/docs" index="1">1</mcreference>

## 4. Ottimizzazione delle Prestazioni

- **Minimizzare i Roundtrip**: Raggruppare le azioni quando possibile. Evitare un uso eccessivo di `wire:model.live` su campi di testo che cambiano frequentemente.
- **`wire:loading`**: Mostrare indicatori di caricamento per dare un feedback visivo all'utente durante le richieste AJAX.
- **Lazy Loading (`wire:init`)**: Utilizzare `wire:init` per caricare dati pesanti solo dopo il rendering iniziale della pagina, evitando di bloccare il caricamento.

## 5. Integrazione con Alpine.js

Livewire 3 si integra nativamente con Alpine.js per le interazioni che non richiedono una comunicazione con il server (es. mostrare/nascondere un menu a tendina). <mcreference link="https://livewire.laravel.com/docs" index="1">1</mference>

- **`@entangle`**: Per sincronizzare una proprietà di Livewire con una variabile di Alpine.js.
- **`$wire`**: Per accedere all'oggetto del componente Livewire dall'interno di Alpine.

Seguire queste regole garantirà che i componenti Livewire siano performanti, sicuri e facili da mantenere nel tempo.