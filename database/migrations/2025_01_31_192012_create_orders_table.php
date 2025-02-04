<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->nullable()->constrained('services')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('user_vehicles')->onDelete('set null');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->foreignId('cy_periodic_id')->nullable()->constrained()->onDelete('set null');

            $table->string('type', 100);
            $table->enum('status', ['pending', 'approved', 'PickUp', 'received', 'completed', 'canceled'])->nullable();
            $table->enum('payment_method', ['Online', 'Cash', 'Wallet'])->nullable();
            $table->enum('type_from', ['Home', 'Center'])->nullable();
            $table->string('position', 100)->nullable();
            $table->date('date_at')->nullable();
            $table->datetime('scheduled_at')->nullable();
            $table->string('time_at', 10)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('lat', 92)->nullable();
            $table->string('long', 92)->nullable();
            $table->string('address_to', 300)->nullable();
            $table->string('lat_to', 92)->nullable();
            $table->string('long_to', 92)->nullable();
            $table->text('details')->nullable();

            $table->foreignId('canceled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('canceled_by_provider')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('update_by')->nullable()->constrained('users')->onDelete('set null');

            $table->decimal('company_profit', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
