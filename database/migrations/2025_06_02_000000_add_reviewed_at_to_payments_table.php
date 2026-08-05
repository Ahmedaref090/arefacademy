<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // When the admin approved/rejected the payment (the audit timestamp).
            // null = still pending review.
            $table->timestamp('reviewed_at')->nullable()->after('rejection_reason');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('reviewed_at');
        });
    }
};
