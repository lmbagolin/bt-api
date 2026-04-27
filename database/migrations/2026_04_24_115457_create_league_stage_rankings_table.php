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
        Schema::create('league_stage_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_stage_id')->constrained('league_stages')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('league_stage_registrations')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unsignedSmallInteger('points');
            $table->unsignedSmallInteger('wins')->default(0);
            $table->unsignedSmallInteger('matches_played')->default(0);
            $table->unsignedSmallInteger('games_pro')->default(0);
            $table->unsignedSmallInteger('games_against')->default(0);
            $table->timestamps();

            $table->unique(['league_stage_id', 'registration_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('league_stage_rankings');
    }
};
