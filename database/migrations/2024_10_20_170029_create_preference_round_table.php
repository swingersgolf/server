<?php

use App\Models\Preference;
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
        Schema::create('preference_round', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('preference_id');
            $table->unsignedBigInteger('round_id');
            $table->enum('status', [Preference::STATUS_DISLIKED, Preference::STATUS_PREFERRED, Preference::STATUS_INDIFFERENT])->default(Preference::STATUS_INDIFFERENT);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preference_round');
    }
};
