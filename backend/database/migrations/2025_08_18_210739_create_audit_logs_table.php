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
        Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Ai thực hiện
        $table->string('event'); // Hành động: created, updated, deleted, logged_in...
        $table->morphs('auditable'); // Đối tượng bị tác động (polymorphic)
        $table->json('old_values')->nullable(); // Dữ liệu cũ
        $table->json('new_values')->nullable(); // Dữ liệu mới
        $table->text('url')->nullable(); // URL nơi hành động xảy ra
        $table->ipAddress('ip_address')->nullable();
        $table->string('user_agent', 1023)->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
