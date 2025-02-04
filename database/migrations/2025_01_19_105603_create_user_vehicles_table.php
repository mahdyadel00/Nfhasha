<?php

use App\Models\User;
use App\Models\VehicleBrand;
use App\Models\VehicleManufactureYear;
use App\Models\VehicleModel;
use App\Models\VehicleType;
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
        Schema::create('user_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->onDelete('cascade');
            $table->string('letters_ar',10);
            $table->string('numbers_ar',10);
            $table->string('letters_en',10);
            $table->string('numbers_en',10);
            $table->foreignIdFor(VehicleType::class)->onDelete('cascade');
            $table->foreignIdFor(VehicleModel::class)->onDelete('cascade');
            $table->foreignIdFor(VehicleManufactureYear::class)->onDelete('cascade');
            $table->foreignIdFor(VehicleBrand::class)->onDelete('cascade');
            $table->date('checkup_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vehicles');
    }
};
