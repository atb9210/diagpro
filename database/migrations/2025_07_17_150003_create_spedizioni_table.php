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
        Schema::create('spedizioni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordini_id')->constrained()->onDelete('cascade');
            $table->string('corriere')->nullable();
            $table->string('numero_tracciamento')->nullable();
            $table->enum('stato', ['in_preparazione', 'spedito', 'consegnato', 'annullato'])->default('in_preparazione');
            $table->date('data_spedizione')->nullable();
            $table->date('data_consegna_prevista')->nullable();
            $table->date('data_consegna_effettiva')->nullable();
            $table->text('indirizzo_spedizione');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spedizioni');
    }
};