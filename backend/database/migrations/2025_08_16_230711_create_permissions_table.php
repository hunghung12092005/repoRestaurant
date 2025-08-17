<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // vd: manage_users
            $table->string('display_name');  // vd: Quản lý Người dùng
            $table->string('description')->nullable(); // Mô tả chi tiết quyền
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('permissions');
    }
};