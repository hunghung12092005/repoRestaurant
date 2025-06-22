<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('room_types', function (Blueprint $table) {
        $table->decimal('rate', 8, 2)->nullable(); // Cột rate
        $table->integer('m2')->nullable(); // Cột diện tích m2
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roomtype', function (Blueprint $table) {
            //
        });
    }
};
