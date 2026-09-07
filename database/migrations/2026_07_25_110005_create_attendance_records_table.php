<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained();
            $table->enum('type', ['check_in', 'check_out']);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('source', ['online', 'offline_sync'])->default('online');
            $table->string('device_identifier')->nullable();
            $table->dateTime('scanned_at');
            $table->date('attendance_date');
            $table->dateTime('synced_at')->nullable();
            $table->enum('sms_status', ['pending', 'sent', 'failed', 'not_applicable'])->default('pending');
            $table->timestamps();

            $table->unique(['student_id', 'type', 'attendance_date']);
            $table->index('attendance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};