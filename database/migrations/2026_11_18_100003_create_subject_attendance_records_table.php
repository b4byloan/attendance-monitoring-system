<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('present');
            $table->date('attendance_date');
            $table->dateTime('scanned_at');
            $table->timestamps();

            // One record per student, subject and day; a second subject scan
            // on the same day is a duplicate, not a second attendance entry.
            $table->unique(['student_id', 'subject_id', 'attendance_date']);
            $table->index(['subject_id', 'attendance_date']);
            $table->index('attendance_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_attendance_records');
    }
};
