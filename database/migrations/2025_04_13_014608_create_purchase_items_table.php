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
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->onDelete('cascade'); // foreign key to purchases table
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // foreign key to products table
            $table->integer('quantity')->default(0); // quantity of the product in the purchase
            $table->decimal('price', 10, 2)->default(0); // price of the product in the purchase
            $table->decimal('total', 10, 2)->default(0); // total price for the quantity of the product
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
