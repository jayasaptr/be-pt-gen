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
            // transaction_type
            $table->enum('transaction_type', ['income', 'expense'])->default('income');
            // reference type nullable
            $table->enum('reference_type', ['sales', 'purchase', 'manual'])->nullable();
            // reference_id nullable
            $table->unsignedBigInteger('reference_id')->nullable();
            // amount
            $table->decimal('amount', 10, 2)->nullable();
            // description
            $table->string('description')->nullable();
            // transaction_date
            $table->date('transaction_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
