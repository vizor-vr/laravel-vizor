<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vizor_watch_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('content_id');
            $table->float('last_position')->default(0);
            $table->float('duration')->default(0);
            $table->integer('watch_count')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'content_id']);
            $table->index('user_id');
        });

        Schema::create('vizor_analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key')->unique();
            $table->json('data');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index('cache_key');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vizor_analytics_cache');
        Schema::dropIfExists('vizor_watch_history');
    }
};
