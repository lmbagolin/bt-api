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
        Schema::table('league_stage_registrations', function (Blueprint $table) {
            $table->foreignId('partner_player_id')
                ->nullable()
                ->after('player_id')
                ->constrained('players')
                ->nullOnDelete();
            $table->string('partner_name')->nullable()->after('partner_player_id');
        });
    }

    public function down(): void
    {
        Schema::table('league_stage_registrations', function (Blueprint $table) {
            $table->dropForeign(['partner_player_id']);
            $table->dropColumn(['partner_player_id', 'partner_name']);
        });
    }
};
