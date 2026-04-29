<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Adiciona coluna gender com enum correto
        Schema::table('leagues', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'mixed'])->nullable()->after('genero');
        });

        // Migra dados convertendo PT → EN
        DB::table('leagues')->update([
            'gender' => DB::raw("CASE genero
                WHEN 'masculino' THEN 'male'
                WHEN 'feminino'  THEN 'female'
                WHEN 'misto'     THEN 'mixed'
                ELSE 'mixed'
            END"),
        ]);

        // Torna não-nulo e remove coluna antiga
        Schema::table('leagues', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'mixed'])->nullable(false)->default('mixed')->change();
            $table->dropColumn('genero');
        });
    }

    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->enum('genero', ['masculino', 'feminino', 'misto'])->nullable()->after('gender');
        });

        DB::table('leagues')->update([
            'genero' => DB::raw("CASE gender
                WHEN 'male'   THEN 'masculino'
                WHEN 'female' THEN 'feminino'
                WHEN 'mixed'  THEN 'misto'
                ELSE 'misto'
            END"),
        ]);

        Schema::table('leagues', function (Blueprint $table) {
            $table->enum('genero', ['masculino', 'feminino', 'misto'])->nullable(false)->default('misto')->change();
            $table->dropColumn('gender');
        });
    }
};
