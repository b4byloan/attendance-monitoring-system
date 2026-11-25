<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            if (! Schema::hasColumn('sections', 'max_capacity')) {
                $table->unsignedInteger('max_capacity')->nullable()->after('school_year');
            }

            if (! Schema::hasColumn('sections', 'status')) {
                $table->string('status', 20)->default('active')->after('max_capacity');
            }
        });

        $hasUnique = collect(Schema::getIndexes('sections'))
            ->contains(fn (array $index): bool => $index['unique'] && $index['columns'] === ['name', 'school_year']);

        if (! $hasUnique) {
            Schema::table('sections', function (Blueprint $table) {
                $table->unique(['name', 'school_year']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropUnique(['name', 'school_year']);
            $table->dropColumn(['max_capacity', 'status']);
        });
    }
};
