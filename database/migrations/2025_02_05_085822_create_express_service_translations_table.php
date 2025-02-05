<?php

use App\Models\ExpressService;
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
        Schema::create('express_service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExpressService::class)->constrained()->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name');
            $table->unique(['express_service_id', 'locale']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('express_service_translations');
    }
};
