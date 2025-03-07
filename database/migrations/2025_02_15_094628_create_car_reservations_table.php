<?php

use App\Models\City;
use App\Models\ExpressService;
use App\Models\User;
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
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(City::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(UserVehicle::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ExpressService::class)->nullable()->constrained()->cascadeOnDelete();
            $table->set('inspection_side', ['all', 'front', 'back', 'sides', 'left'])->default('all');
            $table->date('date')->nullable(false);
            $table->time('time')->nullable(false);
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
