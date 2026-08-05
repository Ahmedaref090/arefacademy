<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Only used when the parent course is per_month. Nullable because
            // lifetime courses have no months. nullOnDelete keeps the lesson
            // if its month is removed (it becomes unassigned).
            $table->foreignId('course_month_id')
                ->nullable()
                ->after('course_id')
                ->constrained('course_months')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_month_id');
        });
    }
};
