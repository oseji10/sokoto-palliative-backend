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
            $table->unsignedBigInteger('beneficiary')->nullable();
            $table->string('transactionId')->nullable();
            $table->unsignedBigInteger('lga')->nullable();
            $table->unsignedBigInteger('soldBy')->nullable();
            $table->string('paymentMethod')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            
            $table->foreign('lga')->references('lgaId')->on('lgas')->onDelete('cascade');
            $table->foreign('soldBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('beneficiary')->references('beneficiaryId')->on('beneficiaries')->onDelete('cascade');
            
            
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
