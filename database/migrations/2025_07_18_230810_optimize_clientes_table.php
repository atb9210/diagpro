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
            $table->enum('tipologia', ['privato', 'azienda'])->default('privato')->after('nome');
            $table->string('ragione_sociale')->nullable()->after('tipologia');
            $table->string('codice_fiscale')->nullable()->after('ragione_sociale');
            $table->string('partita_iva')->nullable()->after('codice_fiscale');
            $table->string('prefisso_telefonico', 10)->default('+39')->after('email');
            
            // Rimuovi il vecchio campo indirizzo
            $table->dropColumn('indirizzo');
            
            // Aggiungi i nuovi campi per l'indirizzo di spedizione
            $table->string('indirizzo_spedizione')->nullable()->after('prefisso_telefonico');
            $table->string('cap_spedizione', 10)->nullable()->after('indirizzo_spedizione');
            $table->string('citta_spedizione')->nullable()->after('cap_spedizione');
            $table->string('provincia_spedizione', 5)->nullable()->after('citta_spedizione');
            $table->string('stato_spedizione', 5)->default('IT')->after('provincia_spedizione');
            
            // Aggiungi i campi per l'indirizzo di fatturazione
            $table->string('indirizzo_fatturazione')->nullable()->after('stato_spedizione');
            $table->string('cap_fatturazione', 10)->nullable()->after('indirizzo_fatturazione');
            $table->string('citta_fatturazione')->nullable()->after('cap_fatturazione');
            $table->string('provincia_fatturazione', 5)->nullable()->after('citta_fatturazione');
            $table->string('stato_fatturazione', 5)->nullable()->after('provincia_fatturazione');
            
            $table->boolean('fatturazione_uguale_spedizione')->default(true)->after('stato_fatturazione');
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
