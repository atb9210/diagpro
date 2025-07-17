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
        Schema::create('ordini_prodotto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordini_id')->constrained()->onDelete('cascade');
            $table->foreignId('prodotto_id')->constrained()->onDelete('cascade');
            $table->integer('quantita')->default(1);
            $table->decimal('prezzo_unitario', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordini_prodotto');
    }
};