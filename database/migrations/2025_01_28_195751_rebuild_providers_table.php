<?php

use App\Models\City;
use App\Models\District;
use App\Models\PickUpTruck;
use App\Models\User;
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
        Schema::dropIfExists('providers');
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(City::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(District::class)->constrained();
            $table->enum('type', ['center', 'individual']);
            $table->boolean('mechanical')->default(false);
            $table->boolean('plumber')->default(false);
            $table->boolean('electrical')->default(false);
            $table->boolean('puncture')->default(false);
            $table->boolean('tow_truck')->default(false);
            $table->boolean('battery')->default(false);
            $table->boolean('fuel')->default(false);
            $table->boolean('pickup')->default(false);
            $table->boolean('open_locks')->default(false);
            $table->boolean('periodic_inspections')->default(false);
            $table->boolean('comprehensive_inspections')->default(false);
            $table->boolean('maintenance')->default(false);
            $table->boolean('car_reservations')->default(false);
            $table->foreignIdFor(PickUpTruck::class)->nullable()->constrained();
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_to')->nullable();
            $table->boolean('home_service')->default(false);
            $table->string('commercial_register')->nullable();
            $table->string('owner_identity')->nullable();
            $table->string('general_license')->nullable();
            $table->string('municipal_license')->nullable();
            $table->boolean('is_active')->default(false);
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
