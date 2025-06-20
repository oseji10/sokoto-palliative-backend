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
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staffId');
            $table->date('effectiveFrom')->nullable();
            $table->date('effectiveUntil')->nullable();
            $table->unsignedBigInteger('userId')->nullable();
            $table->unsignedBigInteger('staffType')->nullable();
            $table->unsignedBigInteger('lga')->nullable();
            $table->unsignedBigInteger('supervisor')->nullable();

         
            $table->string('isActive')->default('true');
            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('staffType')->references('typeId')->on('staff_type')->onDelete('cascade');
            $table->foreign('lga')->references('lgaId')->on('lgas')->onDelete('cascade');
            $table->foreign('supervisor')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            
            
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
