<?php

use App\Models\City;
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
        Schema::create('cy_periodics', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(City::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(UserVehicle::class)->constrained()->cascadeOnDelete();
            $table->decimal('price');
            $table->boolean('status')->default(true);
            $table->decimal('vat')->default(0);
            $table->string('type');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cy_periodics');
    }
};
