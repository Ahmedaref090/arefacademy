<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('sender_details')->nullable()->after('payment_method');
            $table->string('receipt_image_path')->nullable()->after('sender_details');
        });

        // Normalize legacy rows to the new values BEFORE the enum casts apply,
        // so old rows (paid / unpaid / expired / CASH / PAYATFAWRY) never crash casts.
        DB::table('payments')->where('status', 'paid')->update(['status' => 'approved']);
        DB::table('payments')
            ->whereNotIn('status', ['pending', 'approved', 'rejected'])
            ->update(['status' => 'rejected']);
        DB::table('payments')
            ->whereNotNull('payment_method')
            ->whereNotIn('payment_method', ['vodafone_cash', 'instapay'])
            ->update(['payment_method' => null]);

        Schema::table('payments', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();

            // Fawry gateway columns — no longer needed.
            $table->dropColumn(['merchant_ref_number', 'fawry_reference_number', 'fawry_response']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('merchant_ref_number')->nullable();
            $table->string('fawry_reference_number')->nullable();
            $table->json('fawry_response')->nullable();
            $table->dropColumn(['sender_details', 'receipt_image_path']);
        });
    }
};
