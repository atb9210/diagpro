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
        Schema::create('ordini_abbonamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordini_id')->constrained()->onDelete('cascade');
            $table->foreignId('abbonamento_id')->constrained()->onDelete('cascade');
            $table->date('data_inizio');
            $table->date('data_fine')->nullable();
            $table->decimal('prezzo', 10, 2);
            $table->boolean('attivo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordini_abbonamento');
    }
};