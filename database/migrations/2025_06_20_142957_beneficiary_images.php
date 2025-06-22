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
        Schema::create('beneficiary_image', function (Blueprint $table) {
            $table->id('imageId');
            $table->unsignedBigInteger('beneficiaryId')->nullable();
            $table->string('imagePath')->nullable();
            $table->string('imageName')->nullable();
            $table->string('imageType')->nullable();
            $table->timestamps();

            $table->foreign('beneficiaryId')->references('beneficiaryId')->on('beneficiaries')->onDelete('cascade');
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
