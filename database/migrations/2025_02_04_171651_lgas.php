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
        Schema::create('lgas', function (Blueprint $table) {
            $table->id('lgaId');
            $table->unsignedBigInteger('districtId');
            $table->string('lgaName');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('districtId')->references('districtId')->on('senatorial_districts');
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
