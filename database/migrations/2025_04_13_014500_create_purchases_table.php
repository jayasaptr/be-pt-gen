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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade'); // foreign key to suppliers table
            // purchase_date
            $table->date('purchase_date')->nullable(); // date of the purchase
            // total_amount
            $table->decimal('total_amount', 10, 2)->default(0); // total amount of the purchase
            // status
            $table->enum('status', ['pending', 'received', 'canceled'])->default('pending'); // status of the purchase
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
