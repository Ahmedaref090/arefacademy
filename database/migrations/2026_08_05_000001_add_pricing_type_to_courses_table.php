<?php

use App\Enums\PricingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // lifetime = one-time purchase of the whole course
            // per_month = course is split into CourseMonths, bought individually
            $table->string('pricing_type')
                ->default(PricingType::Lifetime->value)
                ->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });
    }
};
