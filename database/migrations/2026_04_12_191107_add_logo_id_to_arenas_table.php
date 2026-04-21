<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('arenas', function (Blueprint $table) {
            $table->foreignId('logo_id')->nullable()->constrained('files')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arenas', function (Blueprint $table) {
            $table->dropForeign(['logo_id']);
            $table->dropColumn('logo_id');
        });
    }
};
