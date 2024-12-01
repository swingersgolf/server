<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReplaceWhenWithDateInRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            // Drop the 'when' column if it exists
            $table->dropColumn('when');

            // Add the 'date' column
            $table->date('date')->nullable(); // Adjust the column type and nullability as needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('rounds', function (Blueprint $table) {
            // Drop the 'date' column if it exists
            $table->dropColumn('date');

            // Add the 'when' column back (assuming it was a string)
            $table->string('when')->nullable(); // Adjust the column type if needed
        });
    }
}
