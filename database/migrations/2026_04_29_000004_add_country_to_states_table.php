<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->char('country_iso3', 3)->default('BRA')->after('name');
            $table->foreign('country_iso3')->references('iso3')->on('countries');
        });

        DB::table('states')->update(['country_iso3' => 'BRA']);
    }

    public function down(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropForeign(['country_iso3']);
            $table->dropColumn('country_iso3');
        });
    }
};
