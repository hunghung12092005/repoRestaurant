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
        Schema::table('booking_hotel', function (Blueprint $table) {
            // Thêm cột room_type sau cột nào đó (ví dụ: sau id, hoặc ở cuối)
            // Bạn có thể chọn kiểu dữ liệu phù hợp, ví dụ string hoặc enum
            $table->string('room_type')->nullable()->after('customer_id'); // Hoặc sau cột khác
            // Thêm cột payment_method
            $table->string('payment_method')->nullable()->after('room_type'); // Hoặc sau cột khác
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_hotel', function (Blueprint $table) {
            // Xóa các cột đã thêm khi rollback migration
            $table->dropColumn('room_type');
            $table->dropColumn('payment_method');
        });
    }
};