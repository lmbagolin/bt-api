<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_stage_group_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('league_stage_groups')->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained('league_stage_registrations')->cascadeOnDelete();
            $table->string('color', 20)->default('#64748b');
            $table->timestamps();

            $table->unique(['group_id', 'registration_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_stage_group_registrations');
    }
};
