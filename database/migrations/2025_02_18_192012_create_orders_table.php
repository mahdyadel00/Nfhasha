<?php

use App\Models\City;
use App\Models\CyPeriodic;
use App\Models\ExpressService;
use App\Models\PickUpTruck;
use App\Models\Provider;
use App\Models\User;
use App\Models\UserVehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignIdFor(User::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(ExpressService::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(UserVehicle::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(City::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignIdFor(CyPeriodic::class)->nullable()->constrained()->onDelete('set null');
            $table->foreignId('pick_up_truck_id')->nullable()->constrained('pick_up_trucks');
            $table->string('type', 100);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed', 'canceled' , 'sent'])->default('pending');
            $table->enum('payment_method', ['Online', 'Cash', 'Wallet'])->nullable();
            $table->string('from_lat', 92)->nullable();
            $table->string('from_long', 92)->nullable();
            $table->string('to_lat', 92)->nullable();
            $table->string('to_long', 92)->nullable();
            $table->text('details')->nullable();
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->string('position')->nullable();
            $table->string('address')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
