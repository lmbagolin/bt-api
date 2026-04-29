<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arenas', function (Blueprint $table) {
            $table->dropColumn('city');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }

    public function down(): void
    {
        Schema::table('arenas', function (Blueprint $table) {
            $table->string('city')->nullable();
        });

        Schema::table('players', function (Blueprint $table) {
            $table->string('city')->nullable();
        });
    }
};
