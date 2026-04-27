<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_stage_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_stage_id')->constrained('league_stages')->cascadeOnDelete();
            $table->string('letter', 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_stage_groups');
    }
};
