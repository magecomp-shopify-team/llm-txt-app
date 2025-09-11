<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('llm_sync_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->timestamp('synced_from')->nullable();
            $table->timestamp('synced_to')->nullable();

            // Resource counts
            $table->integer('products_old')->default(0);
            $table->integer('products_new')->default(0);
            $table->integer('products_added')->default(0);
            $table->integer('products_removed')->default(0);

            $table->integer('collections_old')->default(0);
            $table->integer('collections_new')->default(0);
            $table->integer('collections_added')->default(0);
            $table->integer('collections_removed')->default(0);

            $table->integer('pages_old')->default(0);
            $table->integer('pages_new')->default(0);
            $table->integer('pages_added')->default(0);
            $table->integer('pages_removed')->default(0);

            $table->integer('blogs_old')->default(0);
            $table->integer('blogs_new')->default(0);
            $table->integer('blogs_added')->default(0);
            $table->integer('blogs_removed')->default(0);

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_sync_histories');
    }
};
