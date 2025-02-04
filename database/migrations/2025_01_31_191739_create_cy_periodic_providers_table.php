<?php

use App\Models\CyPeriodic;
use App\Models\Provider;
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
        Schema::create('cy_periodic_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CyPeriodic::class)->constrained();
            $table->foreignIdFor(Provider::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cy_periodic_providers');
    }
};
