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
        Schema::create('preference_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('preference_id');
            $table->uuid('user_id');
            $table->enum('status', ['disliked', 'preferred', 'indifferent'])->default('indifferent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preference_user');
    }
};
