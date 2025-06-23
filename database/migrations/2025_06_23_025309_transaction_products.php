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
         Schema::create('transaction_products', function (Blueprint $table) {
            $table->id();
            $table->string('transactionId')->nullable();
            $table->unsignedBigInteger('productId')->nullable();
            $table->string('quantitySold')->nullable();
            $table->string('cost')->nullable();

            $table->timestamps();

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
