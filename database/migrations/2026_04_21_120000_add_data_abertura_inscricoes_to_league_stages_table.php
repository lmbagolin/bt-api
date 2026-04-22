<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('league_stages', function (Blueprint $table) {
            $table->date('data_abertura_inscricoes')->nullable()->after('data_etapa');
        });
    }

    public function down(): void
    {
        Schema::table('league_stages', function (Blueprint $table) {
            $table->dropColumn('data_abertura_inscricoes');
        });
    }
};
