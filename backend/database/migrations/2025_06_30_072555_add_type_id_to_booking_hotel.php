<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeIdToBookingHotel extends Migration
{
    public function up()
    {
        Schema::table('booking_hotel', function (Blueprint $table) {
            $table->integer('type_id')->nullable()->after('customer_id');
        });
    }

    public function down()
    {
        Schema::table('booking_hotel', function (Blueprint $table) {
            $table->dropColumn('type_id');
        });
    }
}
