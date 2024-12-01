<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->enum('time_range', ['early_bird', 'morning', 'afternoon', 'twilight']);
        });
    }
    
    public function down()
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropColumn('time_range');
        });
    }    
};
