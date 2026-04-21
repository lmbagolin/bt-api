<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('league_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->onDelete('cascade');
            $table->date('data_etapa');
            $table->integer('valor_inscricao');
            $table->string('tipo');
            $table->integer('jogadores_por_grupo');
            $table->integer('classificam_total')->nullable();
            $table->boolean('disputa_3_lugar')->default(true);
            $table->integer('pontuacao_1')->default(150);
            $table->integer('pontuacao_2')->default(100);
            $table->integer('pontuacao_3')->default(75);
            $table->integer('pontuacao_4')->default(75);
            $table->integer('pontuacao_classificados')->default(50);
            $table->integer('pontuacao_fase_grupo')->default(25);
            $table->integer('pontuacao_extra_1_grupo')->default(0);
            $table->enum('sorteio_playoffs', ['aleatorio', 'primeiros_colocados', 'primeiros_com_segundos', 'ordem_classificacao', 'manual']);
            $table->enum('confrontos_playoffs', ['aleatorio', 'primeiros_contra_ultimos', 'manual']);
            $table->enum('sorteio_grupos', ['aleatorio', 'pela_ordem']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_stages');
    }
};
