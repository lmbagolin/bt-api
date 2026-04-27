<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_stage_finalists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_stage_id')->constrained('league_stages')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('league_stage_groups')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('league_stage_registrations')->cascadeOnDelete();
            $table->unsignedSmallInteger('group_position');
            $table->unsignedSmallInteger('pts')->default(0);
            $table->unsignedSmallInteger('gp')->default(0);
            $table->unsignedSmallInteger('gc')->default(0);
            $table->timestamps();

            $table->unique(['league_stage_id', 'registration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_stage_finalists');
    }
};
