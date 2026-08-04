<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Defensive: only adds the manual-receipt columns that are missing,
     * so it is safe whether the payments table is Fawry-era or new.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'payment_method')) {
                // vodafone_cash | instapay
                $table->string('payment_method')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('payments', 'sender_details')) {
                $table->string('sender_details')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('payments', 'receipt_image_path')) {
                $table->string('receipt_image_path')->nullable()->after('sender_details');
            }

            if (! Schema::hasColumn('payments', 'status')) {
                // pending | approved | rejected
                $table->string('status')->default('pending')->after('receipt_image_path');
            }
        });

        // Normalize legacy Fawry-era statuses to the new pending/approved/rejected set.
        if (Schema::hasColumn('payments', 'status')) {
            DB::table('payments')->where('status', 'paid')->update(['status' => 'approved']);
            DB::table('payments')
                ->whereIn('status', ['unpaid', 'expired', 'canceled', 'cancelled', 'refunded', 'failed'])
                ->update(['status' => 'rejected']);
        }

        // Drop Fawry gateway columns. Delete this block if you want to keep the old data.
        Schema::table('payments', function (Blueprint $table) {
            foreach (['merchant_ref_number', 'fawry_reference_number', 'fawry_response'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'merchant_ref_number')) {
                $table->string('merchant_ref_number')->nullable();
            }
            if (! Schema::hasColumn('payments', 'fawry_reference_number')) {
                $table->string('fawry_reference_number')->nullable();
            }
            if (! Schema::hasColumn('payments', 'fawry_response')) {
                $table->json('fawry_response')->nullable();
            }
        });
    }
};
