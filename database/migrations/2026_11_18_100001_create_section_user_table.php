<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['section_id', 'user_id']);
        });

        // Existing single-adviser records become the first entry in the pivot
        // so nobody loses access to the section they already handled.
        DB::table('sections')
            ->whereNotNull('adviser_id')
            ->orderBy('id')
            ->each(function (object $section): void {
                DB::table('section_user')->insertOrIgnore([
                    'section_id' => $section->id,
                    'user_id' => $section->adviser_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_user');
    }
};
