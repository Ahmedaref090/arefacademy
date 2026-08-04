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
        Schema::table('quizzes', function (Blueprint $table) {
            // null = unlimited attempts
            $table->unsignedInteger('max_attempts')->nullable()->after('time_limit_minutes');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Set when the student starts; completed_at stays null while in progress.
            $table->timestamp('started_at')->nullable()->after('answers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('max_attempts');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('started_at');
        });
    }
};
