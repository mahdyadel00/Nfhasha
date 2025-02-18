<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
//    public function up(): void
//    {
//        Schema::table('orders', function (Blueprint $table) {
//            $table->foreignId('pick_up_truck_id')->nullable()->constrained('pick_up_trucks');
//        });
//    }
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'pick_up_truck_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('pick_up_truck_id')->nullable()->constrained('pick_up_trucks');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pick_up_truck_id']);
            $table->dropColumn('pick_up_truck_id');
        });
    }
};
