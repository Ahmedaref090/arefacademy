<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track the last IP a registered device logged in from, so the admin can
     * fingerprint each device on the student profile page (and the device
     * record is persisted on every login, not just device creation).
     */
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->string('last_seen_ip', 45)->nullable()->after('device_name');
        });
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->dropColumn('last_seen_ip');
        });
    }
};