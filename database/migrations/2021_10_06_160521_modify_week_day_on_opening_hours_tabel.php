<?php

use App\Models\OpeningHours;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyWeekDayOnOpeningHoursTabel extends Migration
{
    public function __construct()
    {
        Schema::table('opening_hours', function (Blueprint $table) {
            $table->dropColumn(['week_day']);
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::update("ALTER TABLE `opening_hours` CHANGE `week_day` `week_day` ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '星期'");
        Schema::table('opening_hours', function (Blueprint $table) {
            $table->enum('week_day', ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'])->comment("星期");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opening_hours', function (Blueprint $table) {
            $table->enum('week_day', [
                OpeningHours::MON,
                OpeningHours::TUE,
                OpeningHours::WED,
                OpeningHours::THU,
                OpeningHours::FRI,
                OpeningHours::SAT,
                OpeningHours::SUN,
            ])->comment("星期");
        });
    }
}
