<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('school_name')->default('My School');
            $table->string('school_logo_path')->nullable();
            $table->text('school_address')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('school_contact_number')->nullable();
            $table->string('school_email')->nullable();
            $table->string('academic_year');
            $table->string('timezone')->default('Asia/Manila');
            $table->time('school_start_time')->nullable();
            $table->time('school_end_time')->nullable();

            $table->time('checkin_start_time')->nullable();
            $table->time('checkin_cutoff_time')->nullable();
            $table->time('checkout_start_time')->nullable();
            $table->time('checkout_cutoff_time')->nullable();
            $table->unsignedInteger('late_threshold_minutes')->nullable();

            $table->unsignedInteger('qr_expiration_days')->nullable();
            $table->string('qr_format')->default('png');
            $table->unsignedInteger('qr_default_size')->default(400);

            $table->string('sms_provider')->default('semaphore');
            $table->string('sms_sender_name')->nullable();
            $table->boolean('sms_enabled')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};