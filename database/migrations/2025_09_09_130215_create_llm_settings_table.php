<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('llm_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('include_products')->default(true);
            $table->boolean('include_collections')->default(true);
            $table->boolean('include_pages')->default(false);
            $table->boolean('include_blogs')->default(false);
            $table->string('format')->default('human');
            $table->string('sync_frequency')->default('weekly');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('next_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_settings');
    }
};
