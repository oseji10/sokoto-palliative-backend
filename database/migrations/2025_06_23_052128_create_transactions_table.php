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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->unsignedBigInteger('amount'); // Stored in kobo (1/100 of NGN)
            $table->string('terminal_serial', 64)->index();
            $table->string('payment_type', 32)->default('PURCHASE');
            $table->string('payment_method', 32)->default('ANY');
            $table->enum('status', [
                'PENDING',
                'COMPLETED',
                'CANCELLED',
                'FAILED',
            ])->default('PENDING')->index();
            $table->json('response')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
