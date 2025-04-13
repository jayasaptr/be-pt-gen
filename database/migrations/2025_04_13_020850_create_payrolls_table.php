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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            // employee_id
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            // pay_period (ex: "2025-04")
            $table->string('pay_period');
            // basic_salary
            $table->decimal('basic_salary', 10, 2);
            // deductions
            $table->decimal('deductions', 10, 2)->nullable();
            // bonuses
            $table->decimal('bonuses', 10, 2)->nullable();
            // total_paid
            $table->decimal('total_paid', 10, 2);
            // status (paid/unpaid)
            $table->enum('status', ['paid', 'unpaid'])->default('unpaid');
            // paid_at
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
