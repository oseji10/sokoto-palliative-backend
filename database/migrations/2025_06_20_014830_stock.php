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
        // Create the stock table
        Schema::create('stock', function (Blueprint $table) {
            $table->id('stockId');
            $table->string('stockName')->nullable();
            $table->unsignedBigInteger('productId')->nullable();
            $table->string('batchNumber')->nullable();
            $table->integer('quantityReceived')->default(0)->nullable();
            $table->integer('quantitySold')->default(0)->nullable();
            $table->integer('quantityTransferred')->default(0)->nullable();
            $table->integer('quantityExpired')->default(0)->nullable();
            $table->integer('quantityDamaged')->default(0)->nullable();
            $table->date('expiryDate')->nullable();
            $table->unsignedBigInteger('lgaId')->nullable();
            $table->unsignedBigInteger('receivedBy')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('productId')->references('productId')->on('products')->onDelete('cascade');
            $table->foreign('lgaId')->references('lgaId')->on('lgas')->onDelete('cascade');
            $table->foreign('receivedBy')->references('id')->on('users')->onDelete('cascade');
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
