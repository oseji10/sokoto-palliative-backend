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
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->string('productRequestId')->nullable();
            $table->unsignedBigInteger('productId')->nullable();
            $table->unsignedBigInteger('lga')->nullable();
            $table->string('quantityRequested')->nullable();
            $table->string('quantityReceived')->nullable();
            $table->string('quantityDispatched')->nullable();
            $table->unsignedBigInteger('requestedBy')->nullable();
            $table->unsignedBigInteger('approvedBy')->nullable();
            $table->unsignedBigInteger('receivedBy')->nullable();
            $table->date('requestDate')->nullable();
            $table->date('approvedDate')->nullable();
            $table->date('receivedDate')->nullable();
            $table->timestamps();

            $table->foreign('productId')->references('productId')->on('products')->onDelete('cascade');
            $table->foreign('lga')->references('lgaId')->on('lgas')->onDelete('cascade');
            $table->foreign('requestedBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approvedBy')->references('id')->on('users')->onDelete('cascade');
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
