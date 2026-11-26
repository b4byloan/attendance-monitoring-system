<?php

use App\Models\Section;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bring existing section grade levels in line with the fixed Grade 7-10 list
     * (older records may hold "10" instead of "Grade 10"). Values that cannot be
     * mapped are left untouched so no data is silently rewritten.
     */
    public function up(): void
    {
        DB::table('sections')
            ->select('id', 'grade_level')
            ->orderBy('id')
            ->chunkById(200, function ($sections): void {
                foreach ($sections as $section) {
                    $normalized = Section::normalizeGradeLevel((string) $section->grade_level);

                    if ($normalized === null || $normalized === $section->grade_level) {
                        continue;
                    }

                    DB::table('sections')
                        ->where('id', $section->id)
                        ->update(['grade_level' => $normalized]);
                }
            });
    }

    public function down(): void
    {
        // Data normalization is not reversible.
    }
};
