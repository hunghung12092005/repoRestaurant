<?php 
   use Illuminate\Database\Migrations\Migration;
   use Illuminate\Database\Schema\Blueprint;
   use Illuminate\Support\Facades\Schema;
   
   class AddTurnstileResponseToUsersTable extends Migration
   {
       public function up()
       {
           Schema::table('users', function (Blueprint $table) {
               $table->string('turnstile_response')->nullable(); // Thêm cột mới
           });
       }
   
       public function down()
       {
           Schema::table('users', function (Blueprint $table) {
               $table->dropColumn('turnstile_response'); // Xóa cột nếu cần
           });
       }
   } 
?>