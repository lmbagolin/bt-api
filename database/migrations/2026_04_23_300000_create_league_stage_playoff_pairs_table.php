<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_stage_playoff_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_stage_id')->constrained('league_stages')->cascadeOnDelete();
            $table->foreignId('finalist1_id')->constrained('league_stage_finalists')->cascadeOnDelete();
            $table->foreignId('finalist2_id')->nullable()->constrained('league_stage_finalists')->nullOnDelete();
            $table->unsignedSmallInteger('pair_rank');
            $table->unsignedSmallInteger('pts_total')->default(0);
            $table->unsignedSmallInteger('gp_total')->default(0);
            $table->unsignedSmallInteger('gc_total')->default(0);
            $table->unsignedSmallInteger('position_sum')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_stage_playoff_pairs');
    }
};
