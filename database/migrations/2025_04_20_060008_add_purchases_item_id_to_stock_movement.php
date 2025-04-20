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
            // Add a foreign key column for purchase_item_id
            $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            // Drop the foreign key column for purchase_item_id
            $table->dropForeign(['purchase_item_id']);
            $table->dropColumn('purchase_item_id');
        });
    }
};
