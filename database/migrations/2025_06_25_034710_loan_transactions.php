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
        Schema::create('loan_transactions', function (Blueprint $table) {
    $table->id();
    $table->string('transactionId')->unique();
    $table->unsignedBigInteger('beneficiary');
    $table->unsignedBigInteger('soldBy')->nullable();
    $table->string('paymentMethod');
    $table->string('status')->default('pending');
    $table->decimal('totalCost', 10, 2);
    $table->timestamps();
    $table->foreign('beneficiary')->references('id')->on('users')->onDelete('cascade');
    $table->foreign('soldBy')->references('id')->on('users')->onDelete('set null');
});

Schema::create('loan_transaction_products', function (Blueprint $table) {
    $table->id();
    $table->string('transactionId');
    $table->unsignedBigInteger('productId');
    $table->integer('quantitySold');
    $table->decimal('cost', 10, 2);
    $table->timestamps();
    $table->foreign('transactionId')->references('transactionId')->on('loan_transactions')->onDelete('cascade');
    $table->foreign('productId')->references('productId')->on('products')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
