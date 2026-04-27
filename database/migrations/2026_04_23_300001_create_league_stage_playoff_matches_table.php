<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_stage_playoff_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_stage_id')->constrained('league_stages')->cascadeOnDelete();
            $table->string('round_name', 20); // oitavas, quartas, semi, terceiro, final
            $table->unsignedTinyInteger('match_number');
            $table->foreignId('pair1_id')->nullable()->constrained('league_stage_playoff_pairs')->nullOnDelete();
            $table->foreignId('pair2_id')->nullable()->constrained('league_stage_playoff_pairs')->nullOnDelete();
            $table->boolean('is_bye')->default(false);
            $table->unsignedSmallInteger('score_p')->nullable();
            $table->unsignedSmallInteger('score_q')->nullable();
            $table->foreignId('winner_pair_id')->nullable()->constrained('league_stage_playoff_pairs')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_stage_playoff_matches');
    }
};
