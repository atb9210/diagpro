# Panoramica dello Stack Tecnico del Progetto Diagpro

Questo documento fornisce una visione d'insieme dell'architettura e dello stack tecnologico utilizzato per lo sviluppo del progetto Diagpro. L'obiettivo è offrire un punto di partenza chiaro per qualsiasi sviluppatore che si approccia al codebase.

## Architettura Generale: TALL Stack

Il progetto è costruito seguendo l'approccio **TALL Stack**, un acronimo che rappresenta le tecnologie chiave utilizzate per creare applicazioni web moderne, reattive e full-stack con PHP e JavaScript:

- **T**ailwind CSS: Un framework CSS utility-first per la creazione rapida di interfacce utente personalizzate.
- **A**lpine.js: Un framework JavaScript minimale per comporre comportamenti reattivi direttamente nel markup HTML.
- **L**aravel: Il framework PHP lato server che gestisce il backend, la logica di business, il routing e l'interazione con il database.
- **L**ivewire: Un framework full-stack per Laravel che permette di costruire interfacce dinamiche utilizzando principalmente PHP, riducendo la necessità di scrivere codice JavaScript complesso.

## Componenti Principali dello Stack

1.  **Backend Framework: Laravel 12**
    - **Descrizione**: Laravel è il cuore dell'applicazione, gestendo tutta la logica di business, le API, l'autenticazione, le code e le interazioni con il database tramite l'ORM Eloquent.
    - **Guida di Riferimento**: Per le convenzioni specifiche, la struttura delle directory e le best practice adottate nel progetto, fare riferimento alla guida dettagliata: <mcfile path=".trae/rules/laravel_rules.md" name="laravel_rules.md"></mcfile>.

2.  **Admin Panel: Filament 3**
    - **Descrizione**: L'interfaccia di amministrazione è costruita interamente con Filament 3, un ecosistema basato su Livewire che permette di creare rapidamente pannelli di amministrazione (CRUD, dashboard, widget) con una sintassi fluente e dichiarativa in PHP.
    - **Guida di Riferimento**: Per comprendere come creare e modificare Risorse, Pagine, Widget e utilizzare il Form/Table Builder, consultare la guida completa: <mcfile path=".trae/rules/filament_rules.md" name="filament_rules.md"></mcfile>.

3.  **Frontend Dinamico: Livewire 3**
    - **Descrizione**: Livewire è utilizzato per creare componenti interattivi e reattivi senza dover abbandonare PHP. Gestisce la comunicazione tra frontend e backend in modo trasparente, aggiornando il DOM in modo efficiente quando lo stato di un componente cambia.
    - **Guida di Riferimento**: Per le regole sulla creazione di componenti, la gestione dello stato e l'interazione con Alpine.js, vedere la guida specifica: <mcfile path=".trae/rules/livewire_rules.md" name="livewire_rules.md"></mcfile>.

4.  **Build Tool: Vite.js**
    - **Descrizione**: Vite.js è il tool di build frontend utilizzato per compilare e gestire gli asset (CSS, JavaScript). Offre un'esperienza di sviluppo estremamente rapida grazie al suo server di sviluppo nativo ESM e ottimizza gli asset per la produzione.
    - **Configurazione**: Il file di configurazione principale è `vite.config.js` alla radice del progetto.

## Flusso di Lavoro dello Sviluppo

- **Backend**: La logica viene implementata seguendo i pattern di Laravel (Controller, Model, Service, Policy).
- **Admin Panel**: Le funzionalità CRUD e di gestione vengono aggiunte creando o modificando le Risorse di Filament.
- **Frontend**: L'interattività viene costruita tramite componenti Livewire, con piccole migliorie progressive realizzate con Alpine.js dove necessario.

Consultare le guide specifiche linkate sopra per un approfondimento su ciascuna tecnologia e sulle convenzioni adottate nel progetto.