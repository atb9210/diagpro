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
        Schema::create('impostaziones', function (Blueprint $table) {
            $table->id();
            $table->string('chiave')->unique();
            $table->text('valore');
            $table->string('tipo')->default('string'); // string, number, boolean, json
            $table->string('descrizione')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impostaziones');
    }
};
