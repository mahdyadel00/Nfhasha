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
        Schema::create('type_periodic_inspections_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('type_periodic_inspections')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('locale');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_periodic_inspections_translations');
    }
};
