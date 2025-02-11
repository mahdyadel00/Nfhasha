<?php

use App\Models\ExpressService;
use App\Models\Provider;
use App\Models\User;
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
            $table->string('type', 100);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'completed'])->nullable();
            $table->enum('payment_method', ['Online', 'Cash', 'Wallet'])->nullable();
            $table->string('from_lat', 92)->nullable();
            $table->string('from_long', 92)->nullable();
            $table->string('to_lat', 92)->nullable();
            $table->string('to_long', 92)->nullable();
            $table->text('details')->nullable();
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
