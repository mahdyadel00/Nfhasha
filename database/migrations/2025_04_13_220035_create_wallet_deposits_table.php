<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletDepositsTable extends Migration
{
    public function up()
    {
        Schema::create('wallet_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method'); // visa, mastercard, mada, applepay
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('checkout_id')->nullable(); // Transaction ID من HyperPay
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('wallet_deposits');
    }
}
