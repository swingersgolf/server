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
        Schema::create('message_group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_group_id')->references('id')->on('message_groups');
            $table->foreignUuid('user_id')->references('id')->on('users');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_group_user');
    }
};
