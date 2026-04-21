<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add new fields and unique constraint to players
        Schema::table('players', function (Blueprint $table) {
            $table->string('whatsapp')->nullable()->after('city');
            $table->string('instagram')->nullable()->after('whatsapp');
            
            // Make user_id unique for 1:1 relationship
            // Check if there's already a regular index on user_id and drop it if necessary to avoid conflicts
            // But usually, foreignId creates an index. We want a UNIQUE one now.
            $table->unique('user_id');
        });

        // 2. Create the pivot table arenas_has_players
        Schema::create('arenas_has_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('arena_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Prevent duplicate relations
            $table->unique(['arena_id', 'player_id']);
        });

        // 3. Migrate existing data
        $players = DB::table('players')->whereNotNull('arena_id')->get();
        foreach ($players as $player) {
            DB::table('arenas_has_players')->insert([
                'arena_id' => $player->arena_id,
                'player_id' => $player->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 4. Drop the arena_id column from players
        Schema::table('players', function (Blueprint $table) {
            $table->dropForeign(['arena_id']);
            $table->dropColumn('arena_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('players', function (Blueprint $table) {
            $table->foreignId('arena_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // Restore data from pivot if possible (optional but good practice)
        $relations = DB::table('arenas_has_players')->get();
        foreach ($relations as $relation) {
            DB::table('players')
                ->where('id', $relation->player_id)
                ->update(['arena_id' => $relation->arena_id]);
        }

        Schema::dropIfExists('arenas_has_players');

        Schema::table('players', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropColumn(['whatsapp', 'instagram']);
        });
    }
};
