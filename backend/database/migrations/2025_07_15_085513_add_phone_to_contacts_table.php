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
        Schema::table('contacts', function (Blueprint $table) {
            // Thêm cột 'phone', cho phép giá trị NULL, đặt sau cột 'email'
            $table->string('phone')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            // Xóa cột 'phone' nếu rollback
            $table->dropColumn('phone');
        });
    }
};
