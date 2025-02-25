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
        Schema::create('service_maintenance_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_maintenance_id')->constrained()->cascadeOnDelete();
            $table->string('locale');
            $table->string('name');
            $table->string('description');
            // $table->unique(['service_maintenance_id', 'locale']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_maintenance_translations');
    }
};