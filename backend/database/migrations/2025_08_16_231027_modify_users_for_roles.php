<?php
// database/migrations/xxxx_xx_xx_xxxxxx_modify_users_for_roles.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Xóa 2 cột cũ
            $table->dropColumn(['role', 'permissions']);

            // Thêm cột mới để liên kết với bảng roles
            // onDelete('cascade') có nghĩa là nếu một vai trò bị xóa, tất cả user thuộc vai trò đó cũng sẽ bị xóa.
            // Bạn có thể đổi thành onDelete('set null') nếu muốn giữ lại user và gán vai trò là NULL.
            $table->foreignId('role_id')->nullable()->after('google_id')->constrained('roles')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hoàn tác lại nếu cần
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');

            $table->string('role', 200)->after('google_id');
            $table->longText('permissions')->nullable()->after('role');
        });
    }
};
