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
        Schema::table('ordini_abbonamento', function (Blueprint $table) {
            $table->decimal('costo', 8, 2)->after('prezzo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ordini_abbonamento', function (Blueprint $table) {
            $table->dropColumn('costo');
        });
    }
};
