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
            $table->date('data_arrivo')->nullable()->after('stato');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prodottos', function (Blueprint $table) {
            $table->dropColumn('data_arrivo');
        });
    }
};
