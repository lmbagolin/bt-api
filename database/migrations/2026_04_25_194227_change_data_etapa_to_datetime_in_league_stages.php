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
        Schema::table('league_stages', function (Blueprint $table) {
            $table->dateTime('data_etapa')->change();
        });
    }

    public function down(): void
    {
        Schema::table('league_stages', function (Blueprint $table) {
            $table->date('data_etapa')->change();
        });
    }
};
