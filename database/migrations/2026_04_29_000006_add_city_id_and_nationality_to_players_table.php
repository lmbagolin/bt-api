<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->after('city');
            $table->char('nationality', 3)->nullable()->after('city_id');

            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('nationality')->references('iso3')->on('countries');
        });

        DB::table('players')->update([
            'city_id' => 4205407,
            'nationality' => 'BRA',
        ]);
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['nationality']);
            $table->dropColumn(['city_id', 'nationality']);
        });
    }
};
