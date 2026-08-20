<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Permissions
            $table->boolean('view_server')->default(false);
            $table->boolean('start_server')->default(false);
            $table->boolean('stop_server')->default(false);
            $table->boolean('use_ftp')->default(false);
            $table->boolean('view_ftp_credentials')->default(false);
            $table->boolean('use_web_rcon')->default(false);
            $table->boolean('view_rcon_password')->default(false);
            $table->boolean('view_logs')->default(false);
            $table->boolean('manage_server_users')->default(false);
            
            $table->timestamps();
            
            $table->unique(['server_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_user');
    }
};
