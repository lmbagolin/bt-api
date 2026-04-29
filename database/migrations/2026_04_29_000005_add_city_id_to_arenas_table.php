<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arenas', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->nullable()->after('city');
            $table->foreign('city_id')->references('id')->on('cities');
        });

        DB::table('arenas')->update(['city_id' => 4205407]);
    }

    public function down(): void
    {
        Schema::table('arenas', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
        });
    }
};
