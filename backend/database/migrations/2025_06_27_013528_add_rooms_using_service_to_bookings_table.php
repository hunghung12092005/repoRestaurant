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
    Schema::table('booking_hotel', function (Blueprint $table) {
        $table->json('rooms_using_service')->nullable(); // Cột để lưu số lượng phòng dùng dịch vụ
    });
}

public function down()
{
    Schema::table('booking_hotel', function (Blueprint $table) {
        $table->dropColumn('rooms_using_service');
    });
}
};
