# Regole di Progetto per lo Sviluppo Guidato da AI

Questo documento definisce le regole e le linee guida fondamentali che l'assistente AI deve seguire per garantire uno sviluppo coerente, efficiente e manutenibile del progetto Diagpro.

## 1. Filosofia di Sviluppo: "Filament First"

- **Regola d'oro:** **Usa sempre Filament 3 per qualsiasi interfaccia utente (UI) e operazione CRUD.** L'intero pannello di amministrazione, dalla visualizzazione dei dati alla loro creazione e modifica, deve essere implementato tramite le Risorse, le Pagine e i Widget di Filament.
- **Evita soluzioni custom:** Non creare controller, rotte o viste manuali per funzionalità che possono essere realizzate nativamente con Filament. Questo garantisce coerenza e sfrutta appieno la potenza del framework.
- **Riferimento:** Per dettagli implementativi, consulta sempre la <mcfile path=".trae/rules/filament_rules.md" name="filament_rules.md"></mcfile>.

## 2. Struttura e Logica del Backend

- **Backend in Laravel:** Tutta la logica di business complessa, le interazioni con servizi esterni e le operazioni che non riguardano direttamente la UI devono essere implementate seguendo le best practice di Laravel 12.
- **Eloquent è la via:** Utilizza i modelli Eloquent e le loro relazioni per tutte le interazioni con il database.
- **Logica nei posti giusti:**
    - **Service Classes:** Estrai logiche di business complesse in classi di servizio dedicate.
    - **Lifecycle Hooks di Filament:** Usa i metodi come `mutateFormDataBeforeCreate` o `afterCreate` nelle Risorse di Filament per logiche strettamente legate al ciclo di vita del CRUD.
- **Riferimento:** Per le convenzioni su Laravel, fai riferimento a <mcfile path=".trae/rules/laravel_rules.md" name="laravel_rules.md"></mcfile>.

## 3. Componenti Dinamici e Interattività

- **Livewire per la dinamicità:** Se una funzionalità richiede un'interattività non coperta dai componenti standard di Filament, implementala creando un componente Livewire.
- **Alpine.js per piccole interazioni:** Usa Alpine.js per piccole manipolazioni del DOM o interazioni lato client che non richiedono una comunicazione con il backend.
- **Riferimento:** Le linee guida per la creazione di componenti si trovano in <mcfile path=".trae/rules/livewire_rules.md" name="livewire_rules.md"></mcfile>.

## 4. Gestione del Codice e Versioning

- **Git Flow Semplice:** Lavora sempre sul branch `main`.
- **Commit Semantici:** Scrivi messaggi di commit chiari e significativi, seguendo preferibilmente lo standard [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/). Esempi:
    - `feat: Aggiunta risorsa Prodotti`
    - `fix: Corretto calcolo del totale ordine`
    - `docs: Aggiornata guida di Filament`
    - `refactor: Semplificata logica nel servizio di spedizione`
- **Push Frequenti:** Chiedi il push delle modifiche dopo ogni commit significativo per mantenere il repository remoto aggiornato.

## 5. Regole Generali

- **Non chiedere, fai:** Sii proattivo. Invece di chiedere conferma per ogni passaggio, prendi iniziative basate su queste regole e sulla richiesta originale. Se hai bisogno di informazioni critiche (es. credenziali API), chiedile esplicitamente.
- **Documentazione come guida:** Fai sempre riferimento ai file di documentazione presenti in `.trae/rules/` prima di iniziare a implementare una nuova funzionalità.
- **Mantieni la pulizia:** Commenta il Codice per chiarezza, file non utilizzati e mantieni una formattazione del codice coerente in tutto il progetto.