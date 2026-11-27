<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One-time password fields used to verify newly registered accounts.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code')->nullable()->after('password');
            }
            if (! Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            }
            if (! Schema::hasColumn('users', 'otp_attempts')) {
                $table->unsignedTinyInteger('otp_attempts')->default(0)->after('otp_expires_at');
            }
        });

        // Existing accounts were created before OTP verification existed, so
        // they stay verified instead of being locked out.
        DB::table('users')->whereNull('email_verified_at')->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['otp_code', 'otp_expires_at', 'otp_attempts'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
