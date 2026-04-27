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
        // Convert existing values before altering column
        \DB::statement("UPDATE league_stages SET tipo = 'rei-da-praia' WHERE tipo != 'dupla-fixa' AND tipo != 'simples'");

        Schema::table('league_stages', function (Blueprint $table) {
            $table->enum('tipo', ['rei-da-praia', 'dupla-fixa', 'simples'])->default('rei-da-praia')->change();
        });
    }

    public function down(): void
    {
        Schema::table('league_stages', function (Blueprint $table) {
            $table->string('tipo', 255)->change();
        });
    }
};
