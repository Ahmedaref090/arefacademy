<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track the devices a user has logged in from. Each device is identified
     * by a long-lived encrypted cookie (device_uuid), so dynamic IPs do not
     * create duplicate device entries. A user may hold at most 3 devices.
     */
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Unique per user; identifies the device across logins.
            $table->string('device_uuid', 64);
            // Short user-agent snippet describing the device/browser.
            $table->string('device_name', 255)->nullable();
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'device_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
