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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Add a foreign key column for sales_item_id
            $table->foreignId('sales_item_id')->nullable()->constrained('sales_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movement', function (Blueprint $table) {
            // Drop the foreign key column for sales_item_id
            $table->dropForeign(['sales_item_id']);
            $table->dropColumn('sales_item_id');
        });
    }
};
