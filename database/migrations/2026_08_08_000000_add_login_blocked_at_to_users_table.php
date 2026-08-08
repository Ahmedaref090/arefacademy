<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manually-administered login block. When set, the student cannot log in
     * and sees the locale-aware "maximum devices" message. Set via the admin
     * "Prevent Login" / "Allow Login" toggle on the student profile page.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('login_blocked_at')->nullable()->after('phone_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('login_blocked_at');
        });
    }
};