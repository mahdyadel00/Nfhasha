<?php

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
        Schema::create('express_services', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['open_locks', 'battery', 'fuel', 'puncture', 'tow_truck', 'periodic_inspections' , 'comprehensive_inspections' , 'maintenance' , 'car_reservations' , 'mechanical' , 'plumber' , 'electrical' ]);
            $table->decimal('price', 8, 2)->default(0);
            $table->decimal('vat', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('express_services');
    }
};
