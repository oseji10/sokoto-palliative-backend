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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transactionId')->nullable();
            $table->unsignedBigInteger('productId')->nullable();
            $table->unsignedBigInteger('lga')->nullable();
            $table->string('quantitySold')->nullable();
            $table->string('cost')->nullable();
            $table->unsignedBigInteger('soldBy')->nullable();
            $table->string('paymentMethod')->nullable();
            $table->timestamps();

            $table->foreign('productId')->references('productId')->on('products')->onDelete('cascade');
            $table->foreign('lga')->references('lgaId')->on('lgas')->onDelete('cascade');
            $table->foreign('soldBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approvedBy')->references('id')->on('users')->onDelete('cascade');
            
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
