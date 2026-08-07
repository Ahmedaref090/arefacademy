<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the pivot that consolidates a single receipt (payment) with the
     * one or more course months it covers. A student can pay for several
     * months of a per-month course together and receive ONE receipt.
     */
    public function up(): void
    {
        Schema::create('course_month_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_month_id')->constrained('course_months')->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // A single month can only appear once on a given receipt.
            $table->unique(['course_month_id', 'payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_month_payment');
    }
};