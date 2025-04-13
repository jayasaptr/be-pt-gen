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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // name
            $table->string('name')->nullable();
            // email
            $table->string('email')->unique()->nullable();
            // phone
            $table->string('phone')->nullable();
            // position
            $table->string('position')->nullable();
            // salary
            $table->decimal('salary', 10, 2)->nullable();
            // join_date
            $table->date('join_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
