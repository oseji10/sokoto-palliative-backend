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
        Schema::create('enrollees', function (Blueprint $table) {
            $table->id('enrolleeId');
            $table->string('firstName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('otherNames')->nullable();
            $table->string('phoneNumber')->nullable();
            $table->string('email')->nullable();

            $table->unsignedBigInteger('lga')->nullable();
            $table->unsignedBigInteger('enrolleeType')->nullable();
            $table->unsignedBigInteger('enrolledBy')->nullable();

            $table->foreign('lga')->references('lgaId')->on('lgas')->onDelete('cascade');
            $table->string('isActive')->default('true');
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('enrolleeType')->references('typeId')->on('enrollee_type')->onDelete('cascade');
            $table->foreign('enrolledBy')->references('id')->on('users')->onDelete('cascade');
            
            
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
