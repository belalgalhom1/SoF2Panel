<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->text('start_script')->nullable()->after('rcon_password');
        });

        // For existing servers, we can copy the game's start script.
        DB::statement('UPDATE servers s JOIN games g ON s.game_id = g.id SET s.start_script = g.start_script WHERE s.start_script IS NULL');
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('start_script');
        });
    }
};
