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
        Schema::table('prodottos', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('nome');
            $table->decimal('peso', 8, 3)->nullable()->after('quantita_disponibile'); // in kg
            $table->decimal('lunghezza', 8, 2)->nullable()->after('peso'); // in cm
            $table->decimal('larghezza', 8, 2)->nullable()->after('lunghezza'); // in cm
            $table->decimal('altezza', 8, 2)->nullable()->after('larghezza'); // in cm
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete()->after('altezza');
            $table->foreignId('fornitore_id')->nullable()->constrained('fornitoris')->nullOnDelete()->after('categoria_id');
            $table->json('immagini')->nullable()->after('fornitore_id'); // Array of image paths
            $table->string('immagine_copertina')->nullable()->after('immagini'); // Main image path
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodottos', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropForeign(['fornitore_id']);
            $table->dropColumn([
                'sku',
                'peso',
                'lunghezza',
                'larghezza',
                'altezza',
                'categoria_id',
                'fornitore_id',
                'immagini',
                'immagine_copertina'
            ]);
        });
    }
};
