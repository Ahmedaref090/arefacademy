<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow manual (admin/cash/free) enrollments to be scoped to a specific
     * course month, so a monthly-course enrollment only unlocks its month.
     * course_month_id = null keeps the legacy full-course enrollment.
     *
     * The column may already exist on databases where it was added out-of-band,
     * so every step is guarded.
     */
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'course_month_id')) {
                $table->foreignId('course_month_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('course_months')
                    ->cascadeOnDelete();
            }

            // The new unique starts with user_id, so it keeps satisfying the
            // user_id foreign-key index requirement before we drop the old one.
            if (! Schema::hasIndex('enrollments', ['user_id', 'course_id', 'course_month_id'], 'unique')) {
                $table->unique(['user_id', 'course_id', 'course_month_id']);
            }
            if (Schema::hasIndex('enrollments', ['user_id', 'course_id'], 'unique')) {
                $table->dropUnique('enrollments_user_id_course_id_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasIndex('enrollments', ['user_id', 'course_id', 'course_month_id'], 'unique')) {
                $table->dropUnique('enrollments_user_id_course_id_course_month_id_unique');
            }
            if (! Schema::hasIndex('enrollments', ['user_id', 'course_id'], 'unique')) {
                $table->unique(['user_id', 'course_id']);
            }
            if (Schema::hasColumn('enrollments', 'course_month_id')) {
                $table->dropConstrainedForeignId('course_month_id');
            }
        });
    }
};
