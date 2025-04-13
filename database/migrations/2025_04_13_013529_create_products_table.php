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
            $table->id();
            $table->string('name');
            $table->string('sku');
            // category_id is a foreign key that references the id column in the product_categories table
            $table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade'); ## cascade artinya jika category_id dihapus, maka semua product yang memiliki category_id tersebut juga akan dihapus
            // stock
            $table->integer('stock')->default(0);
            // price
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
