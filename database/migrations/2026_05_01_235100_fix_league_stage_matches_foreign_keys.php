<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $oldForeignKeys = [
            'league_stage_matches_p1_registration_id_foreign',
            'league_stage_matches_p2_registration_id_foreign',
            'league_stage_matches_q1_registration_id_foreign',
            'league_stage_matches_q2_registration_id_foreign',
            'league_stage_matches_d1_player1_id_foreign',
            'league_stage_matches_d1_player2_id_foreign',
            'league_stage_matches_d2_player1_id_foreign',
            'league_stage_matches_d2_player2_id_foreign',
        ];

        foreach ($oldForeignKeys as $fk) {
            try {
                Schema::table('league_stage_matches', function (Blueprint $table) use ($fk) {
                    $table->dropForeign($fk);
                });
            } catch (\Throwable $e) {
                // Ignore if it doesn't exist
            }
        }

        Schema::table('league_stage_matches', function (Blueprint $table) {
            // Re-add correct foreign keys pointing to players table
            $table->foreign('d1_player1_id')->references('id')->on('players')->nullOnDelete();
            $table->foreign('d1_player2_id')->references('id')->on('players')->nullOnDelete();
            $table->foreign('d2_player1_id')->references('id')->on('players')->nullOnDelete();
            $table->foreign('d2_player2_id')->references('id')->on('players')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('league_stage_matches', function (Blueprint $table) {
            $table->dropForeign(['d1_player1_id']);
            $table->dropForeign(['d1_player2_id']);
            $table->dropForeign(['d2_player1_id']);
            $table->dropForeign(['d2_player2_id']);
        });
    }
};
