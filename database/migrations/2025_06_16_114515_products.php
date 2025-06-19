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
        Schema::create('products', function (Blueprint $table) {
            $table->id('productId');
            $table->string('productName')->nullable();
            $table->unsignedBigInteger('productType')->nullable();
            $table->string('cost')->nullable();
            $table->unsignedBigInteger('addedBy')->nullable();
            $table->string('status')->default('active')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            
            $table->foreign('addedBy')->references('id')->on('users');
            $table->foreign('productType')->references('typeId')->on('product_type');
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
