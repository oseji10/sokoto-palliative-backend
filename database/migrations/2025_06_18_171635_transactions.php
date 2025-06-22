<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Schema::create('transactions', function (Blueprint $table) {
        //     $table->id('transactionId');
        //     $table->unsignedBigInteger('enrollee')->nullable();
        //     $table->unsignedBigInteger('product')->nullable();
        //     $table->unsignedBigInteger('seller')->nullable();
        //     $table->string('amountPaid');
        //     $table->timestamps();
        //     $table->softDeletes();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
