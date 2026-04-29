<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
            $table->char('state_code', 2);
            $table->boolean('is_capital');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('state_code')->references('code')->on('states');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
