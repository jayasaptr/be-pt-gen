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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            // employee_id (FK → employees)
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            // date
            $table->date('date')->nullable();
            // check_in_time
            $table->time('check_in_time')->nullable();
            // check_out_time
            $table->time('check_out_time')->nullable();
            // note
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
