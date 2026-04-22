<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_stage_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_stage_id')->constrained('league_stages')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'waitlist', 'cancelled'])->default('pending');
            $table->decimal('valor_pago', 8, 2)->nullable();
            $table->unsignedSmallInteger('posicao_grupo')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['league_stage_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_stage_registrations');
    }
};
