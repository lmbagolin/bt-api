<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Columns were already renamed by a partial prior run; only ensure
        // they are nullable and carry the correct FK to players.
        Schema::table('league_stage_matches', function (Blueprint $table) {
            // Drop leftover indexes with old naming (harmless if already gone)
            foreach ([
                'league_stage_matches_p1_registration_id_foreign',
                'league_stage_matches_p2_registration_id_foreign',
                'league_stage_matches_q1_registration_id_foreign',
                'league_stage_matches_q2_registration_id_foreign',
            ] as $index) {
                try {
                    DB::statement("ALTER TABLE league_stage_matches DROP INDEX `{$index}`");
                } catch (\Throwable) {
                    // already removed
                }
            }

            $table->unsignedBigInteger('d1_player1_id')->nullable()->change();
            $table->unsignedBigInteger('d1_player2_id')->nullable()->change();
            $table->unsignedBigInteger('d2_player1_id')->nullable()->change();
            $table->unsignedBigInteger('d2_player2_id')->nullable()->change();
        });

        // Migrate existing registration IDs → player IDs where possible
        DB::statement("
            UPDATE league_stage_matches m
            LEFT JOIN league_stage_registrations r1 ON m.d1_player1_id = r1.id
            LEFT JOIN league_stage_registrations r2 ON m.d1_player2_id = r2.id
            LEFT JOIN league_stage_registrations r3 ON m.d2_player1_id = r3.id
            LEFT JOIN league_stage_registrations r4 ON m.d2_player2_id = r4.id
            SET
                m.d1_player1_id = r1.player_id,
                m.d1_player2_id = r2.player_id,
                m.d2_player1_id = r3.player_id,
                m.d2_player2_id = r4.player_id
        ");

        Schema::table('league_stage_matches', function (Blueprint $table) {
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

            $table->renameColumn('d1_player1_id', 'p1_registration_id');
            $table->renameColumn('d1_player2_id', 'p2_registration_id');
            $table->renameColumn('d2_player1_id', 'q1_registration_id');
            $table->renameColumn('d2_player2_id', 'q2_registration_id');
        });

        Schema::table('league_stage_matches', function (Blueprint $table) {
            $table->foreign('p1_registration_id')->references('id')->on('league_stage_registrations');
            $table->foreign('p2_registration_id')->references('id')->on('league_stage_registrations');
            $table->foreign('q1_registration_id')->references('id')->on('league_stage_registrations');
            $table->foreign('q2_registration_id')->references('id')->on('league_stage_registrations');
        });
    }
};
