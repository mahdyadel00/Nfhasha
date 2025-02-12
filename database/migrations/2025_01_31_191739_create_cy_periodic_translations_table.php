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
        Schema::create('cy_periodic_translations', function (Blueprint $table) {
            $table->string('locale',5)->index();
            $table->foreignId('cy_periodic_id')->constrained()->onDelete('cascade');
            $table->string('title',150);
            $table->text('terms_conditions')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cy_periodic_translations');
    }
};
