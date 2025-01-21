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
            $table->unsignedBigInteger('message_group_id');
            $table->uuid('user_id');
            $table->foreign('message_group_id')->references('id')->on('message_groups');
            $table->foreign('user_id')->references('id')->on('users');
            $table->boolean('user_active')->default(true);
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
