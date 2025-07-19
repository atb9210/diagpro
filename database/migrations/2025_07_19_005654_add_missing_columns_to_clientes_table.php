<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Aggiungi colonne solo se non esistono
            if (!Schema::hasColumn('clientes', 'tipologia')) {
                $table->enum('tipologia', ['privato', 'azienda'])->default('privato')->after('nome');
            }
            if (!Schema::hasColumn('clientes', 'ragione_sociale')) {
                $table->string('ragione_sociale')->nullable()->after('tipologia');
            }
            if (!Schema::hasColumn('clientes', 'codice_fiscale')) {
                $table->string('codice_fiscale')->nullable()->after('ragione_sociale');
            }
            if (!Schema::hasColumn('clientes', 'partita_iva')) {
                $table->string('partita_iva')->nullable()->after('codice_fiscale');
            }
            if (!Schema::hasColumn('clientes', 'prefisso_telefonico')) {
                $table->string('prefisso_telefonico', 10)->default('+39')->after('email');
            }
            
            // Rimuovi il vecchio campo indirizzo se esiste
            if (Schema::hasColumn('clientes', 'indirizzo')) {
                $table->dropColumn('indirizzo');
            }
            
            // Aggiungi i nuovi campi per l'indirizzo di spedizione
            if (!Schema::hasColumn('clientes', 'indirizzo_spedizione')) {
                $table->string('indirizzo_spedizione')->nullable()->after('prefisso_telefonico');
            }
            if (!Schema::hasColumn('clientes', 'cap_spedizione')) {
                $table->string('cap_spedizione', 10)->nullable()->after('indirizzo_spedizione');
            }
            if (!Schema::hasColumn('clientes', 'citta_spedizione')) {
                $table->string('citta_spedizione')->nullable()->after('cap_spedizione');
            }
            if (!Schema::hasColumn('clientes', 'provincia_spedizione')) {
                $table->string('provincia_spedizione', 5)->nullable()->after('citta_spedizione');
            }
            if (!Schema::hasColumn('clientes', 'stato_spedizione')) {
                $table->string('stato_spedizione', 5)->default('IT')->after('provincia_spedizione');
            }
            
            // Aggiungi i campi per l'indirizzo di fatturazione
            if (!Schema::hasColumn('clientes', 'indirizzo_fatturazione')) {
                $table->string('indirizzo_fatturazione')->nullable()->after('stato_spedizione');
            }
            if (!Schema::hasColumn('clientes', 'cap_fatturazione')) {
                $table->string('cap_fatturazione', 10)->nullable()->after('indirizzo_fatturazione');
            }
            if (!Schema::hasColumn('clientes', 'citta_fatturazione')) {
                $table->string('citta_fatturazione')->nullable()->after('cap_fatturazione');
            }
            if (!Schema::hasColumn('clientes', 'provincia_fatturazione')) {
                $table->string('provincia_fatturazione', 5)->nullable()->after('citta_fatturazione');
            }
            if (!Schema::hasColumn('clientes', 'stato_fatturazione')) {
                $table->string('stato_fatturazione', 5)->nullable()->after('provincia_fatturazione');
            }
            
            if (!Schema::hasColumn('clientes', 'fatturazione_uguale_spedizione')) {
                $table->boolean('fatturazione_uguale_spedizione')->default(true)->after('stato_fatturazione');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'tipologia',
                'ragione_sociale',
                'codice_fiscale', 
                'partita_iva',
                'prefisso_telefonico',
                'indirizzo_spedizione',
                'cap_spedizione',
                'citta_spedizione',
                'provincia_spedizione',
                'stato_spedizione',
                'indirizzo_fatturazione',
                'cap_fatturazione',
                'citta_fatturazione',
                'provincia_fatturazione',
                'stato_fatturazione',
                'fatturazione_uguale_spedizione'
            ]);
            
            // Ripristina il campo indirizzo originale
            $table->text('indirizzo')->nullable();
        });
    }
};
