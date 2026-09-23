<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('session_id')->unique();
            $table->string('ip_address');
            $table->text('user_agent');
            $table->timestamp('login_at');
            $table->timestamp('last_activity')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->text('logout_reason')->nullable();
             $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'status']);
          
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};