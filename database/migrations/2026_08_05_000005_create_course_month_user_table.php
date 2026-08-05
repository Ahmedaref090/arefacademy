<?php

use App\Enums\PurchaseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purchase requests for individual months of PER-MONTH courses.
        Schema::create('course_month_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_month_id')->constrained('course_months')->cascadeOnDelete();
            // pending | approved | rejected (see App\Enums\PurchaseStatus)
            $table->string('status')->default(PurchaseStatus::Pending->value);
            $table->timestamps();

            // One purchase record per student per month.
            $table->unique(['user_id', 'course_month_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_month_user');
    }
};
