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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id('beneficiaryId');
            $table->string('governmentId')->nullable();
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('otherNames')->nullable();
            $table->string('phoneNumber')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('cadre')->nullable();
            $table->unsignedBigInteger('lga')->nullable();
            $table->unsignedBigInteger('beneficiaryType')->nullable();
            $table->unsignedBigInteger('enrolledBy')->nullable();

            $table->foreign('lga')->references('lgaId')->on('lgas')->onDelete('cascade');
            $table->string('isActive')->default('true');
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('beneficiaryType')->references('typeId')->on('beneficiary_type')->onDelete('cascade');
            $table->foreign('enrolledBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('cadre')->references('cadreId')->on('cadre')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
