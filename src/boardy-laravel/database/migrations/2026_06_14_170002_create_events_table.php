<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('title', 200);
            $table->text('description');
            $table->string('location', 255);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('image_url', 500)->nullable();

            $table->timestamps();

            $table->index('starts_at');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
