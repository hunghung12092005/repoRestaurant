<?php 
    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;
    
    return new class extends Migration {
        public function up(): void
        {
            Schema::table('customers', function (Blueprint $table) {
                $table->unsignedBigInteger('room_id')->nullable()->after('address');
    
                // Nếu bạn có bảng `rooms`, thêm dòng dưới để tạo khóa ngoại:
                // $table->foreign('room_id')->references('room_id')->on('rooms')->onDelete('set null');
            });
        }
    
        public function down(): void
        {
            Schema::table('customers', function (Blueprint $table) {
                // Nếu bạn có foreign key
                // $table->dropForeign(['room_id']);
    
                $table->dropColumn('room_id');
            });
        }
    };
    
?>