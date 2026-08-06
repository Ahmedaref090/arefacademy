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
        Schema::table('payments', function (Blueprint $table) {
            // Set only when the payment is for a single month of a per-month
            // course. Null for full-course (lifetime) payments. nullOnDelete
            // keeps the payment record if the month is ever removed.
            $table->foreignId('course_month_id')
                ->nullable()
                ->after('course_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_month_id');
        });
    }
};
