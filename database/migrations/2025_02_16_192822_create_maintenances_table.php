<?php

use App\Models\{ExpressService, PickUpTruck, User, UserVehicle};
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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(UserVehicle::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(PickUpTruck::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignIdFor(ExpressService::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('maintenance_type'); // oil change, tire rotation, etc
            $table->string('maintenance_description');
            $table->string('address');
            $table->string('latitude');
            $table->string('longitude');
            $table->boolean('is_working');
            $table->string('image')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
