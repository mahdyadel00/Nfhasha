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
        Schema::table('f_a_q_s', function (Blueprint $table) {
            if (!Schema::hasColumn('f_a_q_s', 'to_providers')) {
                $table->boolean('to_providers')->default(false);
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('f_a_q_s', function (Blueprint $table) {
            //
        });
    }
};
