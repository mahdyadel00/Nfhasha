<?php

use App\Models\City;
use App\Models\Service;
use App\Models\UserVehicle;
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
        Schema::create('car_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(City::class)->nullable()->nullable();
            $table->foreignIdFor(UserVehicle::class)->nullable()->nullable();
            $table->foreignIdFor(Service::class)->nullable()->nullable();
            $table->enum('inspection_side', ['all', 'front', 'back', 'sides', 'left'])->default('all');
            $table->date('date');
            $table->time('time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_reservations');
    }
};
