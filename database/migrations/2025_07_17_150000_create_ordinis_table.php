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
        Schema::create('ordinis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained()->onDelete('cascade');
            $table->foreignId('traffic_source_id')->nullable()->constrained()->nullOnDelete();
            $table->date('data');
            $table->enum('tipo_vendita', ['online', 'appuntamento', 'contrassegno']);
            $table->string('link_ordine')->nullable();
            $table->decimal('prezzo_vendita', 10, 2);
            $table->decimal('costo_marketing', 10, 2)->default(0);
            $table->decimal('costo_prodotto', 10, 2)->default(0);
            $table->decimal('costo_spedizione', 10, 2)->default(0);
            $table->decimal('altri_costi', 10, 2)->default(0);
            $table->decimal('margine', 10, 2)->default(0);
            $table->boolean('vat')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordinis');
    }
};