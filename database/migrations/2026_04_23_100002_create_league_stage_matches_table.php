<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_stage_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('league_stage_groups')->cascadeOnDelete();
            $table->unsignedTinyInteger('match_number');
            $table->foreignId('p1_registration_id')->constrained('league_stage_registrations');
            $table->foreignId('p2_registration_id')->constrained('league_stage_registrations');
            $table->foreignId('q1_registration_id')->constrained('league_stage_registrations');
            $table->foreignId('q2_registration_id')->constrained('league_stage_registrations');
            $table->unsignedSmallInteger('score_p')->nullable();
            $table->unsignedSmallInteger('score_q')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_stage_matches');
    }
};
