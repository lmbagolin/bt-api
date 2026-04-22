<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('league_stage_players', function (Blueprint $table) {
            $table->dropColumn('confirmado');
            $table->enum('player_status', ['registered', 'alternate', 'confirmed'])
                ->default('registered')
                ->after('player_id');
        });
    }

    public function down(): void
    {
        Schema::table('league_stage_players', function (Blueprint $table) {
            $table->dropColumn('player_status');
            $table->boolean('confirmado')->default(false)->after('player_id');
        });
    }
};
