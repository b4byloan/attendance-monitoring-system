<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->unsignedInteger('max_capacity')->nullable()->after('school_year');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('max_capacity');
            $table->unique(['name', 'school_year']);
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique(['name', 'school_year']);
            $table->dropColumn(['max_capacity', 'status']);
        });
    }
};
