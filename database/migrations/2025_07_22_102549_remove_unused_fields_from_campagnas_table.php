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
        Schema::table('campagnas', function (Blueprint $table) {
            $table->dropColumn(['costo_conversione', 'margine']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campagnas', function (Blueprint $table) {
            $table->decimal('costo_conversione', 10, 2)->nullable();
            $table->decimal('margine', 10, 2)->nullable();
        });
    }
};
