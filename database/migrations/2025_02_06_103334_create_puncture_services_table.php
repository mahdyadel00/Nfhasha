<?php

use App\Models\{ExpressService, User,PickUpTruck,UserVehicle};
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
        Schema::create('puncture_services', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExpressService::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(UserVehicle::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PickUpTruck::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('address');
            $table->string('distanition')->nullable();
            $table->string('from_latitude');
            $table->string('from_longitude');
            $table->string('to_latitude')->nullable();
            $table->string('to_longitude')->nullable();
            $table->enum('type_battery', ['original', 'commercial'])->nullable();
            $table->string('battery_image')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('amount', 8, 2)->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed' , 'sent'])->default('pending');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puncture_services');
    }
};
